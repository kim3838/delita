<?php

namespace App\Http\Controllers;

use App\Exports\BlankEmployeeTemplateExport;
use Maatwebsite\Excel\Excel as Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class EmployeeImportTemplateController extends Controller
{
    public function index(): BinaryFileResponse
    {
        return ExcelFacade::download(new BlankEmployeeTemplateExport, 'employee-import-template.csv', Excel::CSV);
    }
}
