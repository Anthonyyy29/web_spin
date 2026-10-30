<?php

use App\Enums\AttendanceStatus;
use App\Support\AttendanceSpreadsheet;
use Illuminate\Testing\TestResponse;

it('writes a titled table with merged header cells and frozen panes', function () {
    $response = TestResponse::fromBaseResponse(AttendanceSpreadsheet::download(
        'contoh.xlsx',
        'Judul contoh',
        [['Username', 'Role', '12/10/2026', null, 'Hadir'], [null, null, 'Pagi Ceria', 'Malam Minggu', null]],
        [['ani', 'Announcer', AttendanceStatus::Hadir, '–', 2]],
        [[0, 0, 0, 1], [2, 0, 3, 0]],
        frozenColumns: 2,
    ));

    $sheet = readSpreadsheet($response);

    expect($response->headers->get('Content-Type'))->toBe('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
        ->and($response->headers->get('Content-Disposition'))->toContain('contoh.xlsx')
        ->and($sheet['rows'][0][0])->toBe('Judul contoh')
        ->and($sheet['rows'][1])->toBe([])
        ->and($sheet['rows'][2])->toBe(['Username', 'Role', '12/10/2026', '', 'Hadir'])
        ->and($sheet['rows'][3])->toBe(['', '', 'Pagi Ceria', 'Malam Minggu', ''])
        ->and($sheet['rows'][4])->toBe(['ani', 'Announcer', 'Hadir', '–', 2])
        ->and($sheet['merges'])->toEqualCanonicalizing(['A3:A4', 'C3:D3'])
        ->and($sheet['sheetXml'])->toContain('xSplit="2"')->toContain('ySplit="4"')
        ->and($sheet['stylesXml'])->toContain('DCFCE7')->toContain('E2E5E9');
});

it('freezes only the header rows when no column is frozen', function () {
    $sheet = readSpreadsheet(TestResponse::fromBaseResponse(
        AttendanceSpreadsheet::download('a.xlsx', 'Judul', [['Status']], [[AttendanceStatus::Belum]]),
    ));

    expect($sheet['sheetXml'])->toContain('ySplit="3"')->not->toContain('xSplit=');
});

it('keeps user text that looks like a formula as plain text', function () {
    $sheet = readSpreadsheet(TestResponse::fromBaseResponse(
        AttendanceSpreadsheet::download('a.xlsx', '=1+1', [['Catatan']], [['=HYPERLINK("http://x","klik")']]),
    ));

    expect($sheet['sheetXml'])->not->toContain('<f>')
        ->and($sheet['rows'][0][0])->toBe('=1+1')
        ->and($sheet['rows'][3][0])->toBe('=HYPERLINK("http://x","klik")');
});

it('colours each attendance status', function (AttendanceStatus $status, string $background, string $font, bool $bold) {
    $style = AttendanceSpreadsheet::statusStyle($status);

    expect($style->backgroundColor)->toBe($background)
        ->and($style->fontColor)->toBe($font)
        ->and($style->fontBold)->toBe($bold);
})->with([
    [AttendanceStatus::Hadir, 'DCFCE7', '166534', false],
    [AttendanceStatus::Terlambat, 'FEF3C7', '92400E', false],
    [AttendanceStatus::Izin, 'DBEAFE', '1E40AF', false],
    [AttendanceStatus::Sakit, 'DBEAFE', '1E40AF', false],
    [AttendanceStatus::TidakHadir, 'FEE2E2', 'B42318', true],
    [AttendanceStatus::Belum, 'F4F5F7', '5F6875', false],
]);
