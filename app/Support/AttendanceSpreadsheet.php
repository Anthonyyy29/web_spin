<?php

namespace App\Support;

use App\Enums\AttendanceStatus;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Cell\EmptyCell;
use OpenSpout\Common\Entity\Cell\NumericCell;
use OpenSpout\Common\Entity\Cell\StringCell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\Common\Helper\CellHelper;
use OpenSpout\Writer\XLSX\Entity\SheetView;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class AttendanceSpreadsheet
{
    /** Baris sheet tempat header pertama berada (judul + baris kosong di atasnya). */
    private const HEADER_OFFSET = 2;

    /**
     * Stream one attendance table as an .xlsx download.
     *
     * Layout: title row, one empty row, header rows, then body rows. Header merges are
     * 0-based and relative to the first header row.
     *
     * @param  list<list<string|null>>  $headerRows
     * @param  iterable<list<string|int|AttendanceStatus|null>>  $bodyRows
     * @param  list<array{0: int, 1: int, 2: int, 3: int}>  $headerMerges  [startColumn, startRow, endColumn, endRow]
     */
    public static function download(
        string $filename,
        string $title,
        array $headerRows,
        iterable $bodyRows,
        array $headerMerges = [],
        int $frozenColumns = 0,
    ): StreamedResponse {
        return response()->streamDownload(function () use ($title, $headerRows, $bodyRows, $headerMerges, $frozenColumns): void {
            $options = new Options;

            $columnCount = count($headerRows[0] ?? []);
            for ($column = 1; $column <= $columnCount; $column++) {
                $options->setColumnWidth($column <= $frozenColumns ? 22 : 14, $column);
            }

            foreach ($headerMerges as [$startColumn, $startRow, $endColumn, $endRow]) {
                $options->mergeCells(
                    $startColumn,
                    $startRow + self::HEADER_OFFSET + 1,
                    $endColumn,
                    $endRow + self::HEADER_OFFSET + 1,
                );
            }

            $writer = new Writer($options);
            $writer->openToFile('php://output');

            $sheetView = (new SheetView)->withFreezeRow(self::HEADER_OFFSET + 1 + count($headerRows));
            if ($frozenColumns > 0) {
                $sheetView = $sheetView->withFreezeColumn(CellHelper::getColumnLettersFromColumnIndex($frozenColumns));
            }
            $writer->getCurrentSheet()->setSheetView($sheetView);

            $writer->addRow(new Row([new StringCell($title, new Style(fontBold: true, fontSize: 14))]));
            $writer->addRow(new Row([]));

            $headerStyle = new Style(fontBold: true, backgroundColor: 'E2E5E9');
            foreach ($headerRows as $headerRow) {
                $writer->addRow(new Row(array_map(
                    fn (?string $value) => $value === null
                        ? new EmptyCell(null, $headerStyle)
                        : new StringCell($value, $headerStyle),
                    $headerRow,
                )));
            }

            foreach ($bodyRows as $bodyRow) {
                $writer->addRow(new Row(array_map(self::bodyCell(...), $bodyRow)));
            }

            $writer->close();
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public static function statusStyle(AttendanceStatus $status): Style
    {
        return match ($status) {
            AttendanceStatus::Hadir => new Style(fontColor: '166534', backgroundColor: 'DCFCE7'),
            AttendanceStatus::Terlambat => new Style(fontColor: '92400E', backgroundColor: 'FEF3C7'),
            AttendanceStatus::Izin, AttendanceStatus::Sakit => new Style(fontColor: '1E40AF', backgroundColor: 'DBEAFE'),
            AttendanceStatus::TidakHadir => new Style(fontBold: true, fontColor: 'B42318', backgroundColor: 'FEE2E2'),
            AttendanceStatus::Belum => new Style(fontColor: '5F6875', backgroundColor: 'F4F5F7'),
        };
    }

    private static function bodyCell(string|int|AttendanceStatus|null $value): Cell
    {
        return match (true) {
            $value instanceof AttendanceStatus => new StringCell($value->label(), self::statusStyle($value)),
            is_int($value) => new NumericCell($value),
            $value === null => new EmptyCell(null),
            default => new StringCell($value),
        };
    }
}
