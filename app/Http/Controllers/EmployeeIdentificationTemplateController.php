<?php

namespace App\Http\Controllers;

use App\Exports\BlankEmployeeIdentificationTemplateExport;
use Maatwebsite\Excel\Excel as Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use PhpOffice\PhpSpreadsheet\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class EmployeeIdentificationTemplateController extends Controller
{
    /**
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function index(): BinaryFileResponse
    {
        return ExcelFacade::download(new BlankEmployeeIdentificationTemplateExport(), 'employee-identification-template.csv', Excel::CSV);
    }
}
