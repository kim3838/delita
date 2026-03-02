<?php

namespace App\Http\Controllers\Internal;

class LogDownloadController
{
    public function downloadErrors()
    {
        $zip_file = 'laravel-logs-' . now()->format('Y-m-d_H-i-s') . '.zip';
        $zip = new \ZipArchive();
        $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        $path = storage_path() . '/logs/error';

        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));

        foreach ($files as $name => $file)
        {
            // We're skipping all subfolders
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();

                $zip->addFile($filePath, $file->getFilename());
            }
        }
        $zip->close();
        return response()->download($zip_file);
    }

    public function downloadDebugs()
    {
        $zip_file = 'laravel-logs-' . now()->format('Y-m-d_H-i-s') . '.zip';
        $zip = new \ZipArchive();
        $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        $path = storage_path() . '/logs/debug';

        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));

        foreach ($files as $name => $file)
        {
            // We're skipping all subfolders
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();

                $zip->addFile($filePath, $file->getFilename());
            }
        }
        $zip->close();
        return response()->download($zip_file);
    }

    public function downloadAuths()
    {
        $zip_file = 'laravel-logs-' . now()->format('Y-m-d_H-i-s') . '.zip';
        $zip = new \ZipArchive();
        $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        $path = storage_path() . '/logs/auth';

        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));

        foreach ($files as $name => $file)
        {
            // We're skipping all subfolders
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();

                $zip->addFile($filePath, $file->getFilename());
            }
        }
        $zip->close();
        return response()->download($zip_file);
    }
}
