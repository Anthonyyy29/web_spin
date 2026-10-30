<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use OpenSpout\Reader\XLSX\Options as ReaderOptions;
use OpenSpout\Reader\XLSX\Reader;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
 // ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Read the first sheet of a downloaded .xlsx response.
 *
 * @return array{rows: list<list<mixed>>, merges: list<string>, sheetXml: string, stylesXml: string}
 */
function readSpreadsheet(TestResponse $response): array
{
    $path = tempnam(sys_get_temp_dir(), 'xlsx').'.xlsx';
    file_put_contents($path, $response->streamedContent());

    $reader = new Reader(new ReaderOptions(SHOULD_PRESERVE_EMPTY_ROWS: true, SHOULD_LOAD_MERGE_CELLS: true));
    $reader->open($path);

    $rows = [];
    $merges = [];

    foreach ($reader->getSheetIterator() as $sheet) {
        foreach ($sheet->getRowIterator() as $row) {
            $rows[] = $row->toArray();
        }
        $merges = $sheet->getMergeCells();

        break;
    }

    $reader->close();

    $zip = new ZipArchive;
    $zip->open($path);
    $sheetXml = (string) $zip->getFromName('xl/worksheets/sheet1.xml');
    $stylesXml = (string) $zip->getFromName('xl/styles.xml');
    $zip->close();

    unlink($path);

    return ['rows' => $rows, 'merges' => $merges, 'sheetXml' => $sheetXml, 'stylesXml' => $stylesXml];
}
