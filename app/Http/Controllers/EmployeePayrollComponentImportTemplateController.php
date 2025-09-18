<?php

namespace App\Http\Controllers;

use App\Exports\BlankEmployeePayrollComponentTemplateExport;
use Maatwebsite\Excel\Excel as Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class EmployeePayrollComponentImportTemplateController extends Controller
{
    public function index(): BinaryFileResponse
    {
        return ExcelFacade::download(new BlankEmployeePayrollComponentTemplateExport(), 'employee-payroll-component-import-template.csv', Excel::CSV);
    }
}
