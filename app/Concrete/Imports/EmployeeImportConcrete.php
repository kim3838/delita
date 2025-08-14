<?php

namespace App\Concrete\Imports;

use App\Blueprint\Imports\EmployeeImport;
use App\Concrete\BaseImportConcrete;
use App\Exports\BlankEmployeeTemplateExport;
use App\Models\Company;
use App\Models\Employee;

class EmployeeImportConcrete extends BaseImportConcrete implements EmployeeImport
{
    public function exportTemplate(): string
    {
        return BlankEmployeeTemplateExport::class;
    }

    public function validateData($data, $companyId): array
    {
        $dataToImport = [];
        $existingNumbers = $this->getExistingEmployeeNumbers($companyId);
        $fileNumbers = [];

        foreach ($data as $index => $row) {

            $validationErrors = [];

            if (empty($row['number'])) {
                $validationErrors[] = 'Number is required';
            } else {
                if (in_array($row['number'], $existingNumbers)) {
                    $validationErrors[] = 'Number already exists in the system.';
                }

                if (in_array($row['number'], $fileNumbers)) {
                    $validationErrors[] = 'Duplicate number in the file.';
                } else {
                    $fileNumbers[] = $row['number'];
                }
            }

            if (empty($row['family_name'])) {
                $validationErrors[] = 'Family name is required.';
            }

            if (empty($row['given_name'])) {
                $validationErrors[] = 'Given name is required.';
            }

            $row['validation_errors'] = $validationErrors;

            $dataToImport[] = $row;
        }

        return $dataToImport;
    }

    public function getExistingEmployeeNumbers($companyId): array
    {
        return Employee::where('company_id', $companyId)
            ->pluck('number')
            ->toArray();
    }

    public function resolvedData($data, $companyId): array
    {
        $employeesData = array_map(function ($row){
            return [
                'number' => $row['number'],
                'family_name' => $row['family_name'],
                'given_name' => $row['given_name']
            ];
        }, $data);

        Company::find($companyId)->employees()->createMany($employeesData);

        return array_map(function ($row) {return $row['id'];}, $data);
    }
}
