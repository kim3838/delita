<?php

namespace App\Http\Controllers;

use App\Exports\BlankEmploymentProfileTemplateExport;
use Maatwebsite\Excel\Excel as Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class EmploymentProfileImportTemplateController extends Controller
{
    public function index(): BinaryFileResponse
    {
        return ExcelFacade::download(new BlankEmploymentProfileTemplateExport(), 'employment-profile-import-template.csv', Excel::CSV);
    }
}
