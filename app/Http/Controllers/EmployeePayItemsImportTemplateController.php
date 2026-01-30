<?php

namespace App\Http\Controllers;

use App\Exports\BlankEmployeePayItemsTemplateExport;
use Maatwebsite\Excel\Excel as Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class EmployeePayItemsImportTemplateController extends Controller
{
    public function index(): BinaryFileResponse
    {
        return ExcelFacade::download(new BlankEmployeePayItemsTemplateExport(), 'employee-pay-items-import-template.csv', Excel::CSV);
    }
}
