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
     * Simpan berkas ke public/assets/uploads (atau subfolder) dan kembalikan path
     * relatif terhadap folder itu, mis. "crew/1700000000_ab12.png".
     */
    protected function storeUpload(UploadedFile $file, string $subfolder = ''): string
    {
        $name = time().'_'.bin2hex(random_bytes(10)).'.'.$file->getClientOriginalExtension();
        $file->move(public_path(trim(self::UPLOAD_FOLDER.'/'.$subfolder, '/')), $name);

        return trim($subfolder.'/'.$name, '/');
    }

    /**
     * Hapus berkas berdasarkan path relatif yang dikembalikan storeUpload.
     */
    protected function deleteUpload(?string $path): void
    {
        if ($path !== null && $path !== '') {
            @unlink(public_path(self::UPLOAD_FOLDER.'/'.ltrim(str_replace('..', '', $path), '/')));
        }
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
