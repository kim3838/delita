<?php

namespace App\Http\Controllers;

use App\Exports\BlankOvertimeTemplateExport;
use Maatwebsite\Excel\Excel as Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use PhpOffice\PhpSpreadsheet\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class OvertimeImportTemplateController extends Controller
{
    /**
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function index(): BinaryFileResponse
    {
        return ExcelFacade::download(new BlankOvertimeTemplateExport(), 'overtime-import-template.csv', Excel::CSV);
    }
}
