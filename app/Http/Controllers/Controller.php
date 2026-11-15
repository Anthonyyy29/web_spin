<?php

namespace App\Http\Controllers;

use Illuminate\Http\UploadedFile;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

abstract class Controller
{
    protected const string UPLOAD_FOLDER = 'assets/uploads';

    /**
     * Simpan berkas ke public/assets/uploads (atau subfolder) dan kembalikan nama barunya.
     */
    protected function storeUpload(UploadedFile $file, string $subfolder = ''): string
    {
        $name = time().'_'.bin2hex(random_bytes(10)).'.'.$file->getClientOriginalExtension();
        $file->move($this->uploadPath($subfolder), $name);

        return $name;
    }

    protected function deleteUpload(?string $name, string $subfolder = ''): void
    {
        if ($name !== null && $name !== '') {
            @unlink($this->uploadPath($subfolder).'/'.basename($name));
        }
    }

    private function uploadPath(string $subfolder): string
    {
        return public_path(trim(self::UPLOAD_FOLDER.'/'.$subfolder, '/'));
    }

    /**
     * Unduh data sebagai file .xlsx; kolom pertama berisi nomor urut bila $numbered.
     *
     * @param  list<string>  $headers
     * @param  list<list<mixed>>  $rows
     */
    protected function downloadExcel(string $filename, array $headers, array $rows, bool $numbered = false): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows, $numbered): void {
            $writer = new Writer;
            $writer->openToFile('php://output');
            $writer->addRow(Row::fromValues($numbered ? ['No.', ...$headers] : $headers));

            foreach ($rows as $index => $row) {
                $writer->addRow(Row::fromValues($numbered ? [$index + 1, ...$row] : $row));
            }

            $writer->close();
        }, $filename.'.xlsx', ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }
}
