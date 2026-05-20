<?php

namespace App\Services\Export;

use Maatwebsite\Excel\Facades\Excel;

class ExcelExportService
{
    public function download(
        string $fileName,
        array $headers,
        array $rows
    ) {
        return Excel::download(
            new ArrayExport($headers, $rows),
            $fileName
        );
    }
}
