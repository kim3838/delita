<?php

namespace App\Http\Controllers;

use App\Exports\BlankAttendanceTemplateExport;
use Maatwebsite\Excel\Excel as Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use PhpOffice\PhpSpreadsheet\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AttendanceImportTemplateController extends Controller
{
    /**
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function index(): BinaryFileResponse
    {
        return ExcelFacade::download(new BlankAttendanceTemplateExport(), 'attendance-import-template.csv', Excel::CSV);
    }
}
