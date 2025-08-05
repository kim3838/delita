<?php

namespace App\Http\Controllers\Imports;

use App\Exports\BlankEmployeeTemplateExport;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Imports\EmployeesImport;
use App\Imports\EmployeesImportArray;
use App\Imports\EmployeesImportCollection;
use App\Imports\EmployeesImportRows;
use App\Models\Employee;
use App\Transformers\Employee\ImportBasicTransformer;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\HeadingRowImport;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;
use Maatwebsite\Excel\Validators\ValidationException as ExcelValidationException;

class EmployeeController extends ImportController
{
    public function preImport(Request $request)
    {
        if($request->expectsJson()){

            $fileToImport = request()->file('file');

            // Validate the uploaded file
            $request->validate([
                'company_id' => 'required|exists:companies,id',
                'file' => [
                    'required',
                    'file',
                    'max:20480', // 20MB in kilobytes
                    'mimes:csv,xlsx,xls',
                    'mimetypes:text/csv,text/plain,application/csv,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                ]
            ], [
                'company_id.required' => 'Company is required.',
                'company_id.exists' => 'Company not found.',
                'file.required' => 'Please select a file to import.',
                'file.file' => 'The uploaded file is not valid.',
                'file.max' => 'The file size must not exceed 20MB.',
                'file.mimes' => 'Only CSV and Excel files (xlsx, xls) are allowed.',
                'file.mimetypes' => 'Invalid file format. Only CSV and Excel files are supported.'
            ]);

            $sheetIndex = 0;
            $headingRowIndex = 0;
            $templateHeadings = app(BlankEmployeeTemplateExport::class)->headings();
            $templateHeadingsFormatted = HeadingRowFormatter::format($templateHeadings);

            $importHeadings = new HeadingRowImport()->toArray($fileToImport);
            $importHeadingsValid = $templateHeadingsFormatted === $importHeadings[$sheetIndex][$headingRowIndex];

            if(!$importHeadingsValid){

                return ResponseJson::validationErrorResponse([], 'Invalid headings.');
            }

            $failures = [];

            try{

                Excel::import(new EmployeesImportArray(request()->company_id), $fileToImport);
                //Todo: Add validation on number, must be unique within the imported file.

            } catch(ExcelValidationException $exception){
                $failures = $exception->failures();

                usort($failures, function($a, $b) {
                    return $a->row() <=> $b->row();
                });
            }

            if(count($failures)){
                return ResponseJson::validationErrorResponse($failures);
            }

            try{
                $import = new EmployeesImport(request()->company_id);

                Excel::import($import, $fileToImport);
                $employees = Excel::toArray($import, $fileToImport);

            } catch(ExcelValidationException $exception){
                $failures = $exception->failures();
            }

            if(count($failures)){
                return ResponseJson::validationErrorResponse($failures);
            }

            $employees = Employee::hydrate($employees[$sheetIndex]);

            return ResponseJson::successfulResponse(
                Fractal::collection($employees, ImportBasicTransformer::class, 'employees')
            );
        }

        abort(404);
    }
}
