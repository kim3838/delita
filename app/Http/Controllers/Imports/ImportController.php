<?php

namespace App\Http\Controllers\Imports;

use App\Http\Controllers\Controller;
use Illuminate\Http\UploadedFile;

class ImportController extends Controller
{
    protected array $allowedExtensions = ['csv', 'xlsx', 'xls'];

    protected array $allowedMimeTypes = [
        'text/csv',
        'text/plain',
        'application/csv',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    ];

    /**
     * Get the appropriate reader type based on file extension
     * Only supports CSV, XLSX, and XLS formats
     *
     * @param UploadedFile $file
     * @return string
     */
    protected function getReaderType(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());

        return match ($extension) {
            'csv' => \Maatwebsite\Excel\Excel::CSV,
            'xlsx' => \Maatwebsite\Excel\Excel::XLSX,
            'xls' => \Maatwebsite\Excel\Excel::XLS,
            default => \Maatwebsite\Excel\Excel::CSV,
        };
    }

}
