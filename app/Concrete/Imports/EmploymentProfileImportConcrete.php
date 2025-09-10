<?php

namespace App\Concrete\Imports;

use App\Blueprint\Imports\EmploymentProfileImport;
use App\Concrete\BaseImportConcrete;
use App\Enums\EmploymentStatus;
use App\Enums\EmploymentType;
use App\Enums\EndOfServiceType;
use App\Exports\BlankEmploymentProfileTemplateExport;
use App\Models\Employee;
use Carbon\Carbon;

class EmploymentProfileImportConcrete extends BaseImportConcrete implements EmploymentProfileImport
{
    public function exportTemplate(): string
    {
        return BlankEmploymentProfileTemplateExport::class;
    }

    public function validateData($data, $companyId): array
    {
        $dataToImport = [];
        _debug([
            'validateData' => $data
        ]);
        foreach ($data as $index => $row) {

            $validationErrors = [];

            if (empty($row['employee_number'])) {
                $validationErrors[] = 'Employee number is required.';
            } else {

                $employee = Employee::query()
                    ->where('company_id', $companyId)
                    ->where('number', $row['employee_number'])
                    ->first();

                if (empty($employee)) {
                    $validationErrors[] = 'Employee not found';
                }
            }

            if (empty($row['employment_type'])) {
                $validationErrors[] = 'Employment type is required.';
            } else {

                $employmentTypeValid = isNameInEnum(EmploymentType::class, $row['employment_type']);

                if(!$employmentTypeValid){
                    $validationErrors[] = 'Employment type invalid.';
                }
            }

            if (empty($row['start_date'])) {
                $validationErrors[] = 'Start date is required.';
            } else {

                try {

                    $date = Carbon::parse($row['start_date']);

                    $row['start_date'] = $date->toDateString();

                } catch (\Exception $e) {
                    $validationErrors[] = 'Start date invalid.' ;
                }
            }

            if (!empty($row['end_of_service_type'])) {

                $endOfServiceTypeValid = isNameInEnum(EndOfServiceType::class, $row['end_of_service_type']);

                if(!$endOfServiceTypeValid){

                    $validationErrors[] = 'End of service type invalid.';
                }

                if (empty($row['end_date'])) {
                    $validationErrors[] = 'End date is required.';
                } else {

                    try {

                        $date = Carbon::parse($row['end_date']);

                        $row['end_date'] = $date->toDateString();

                    } catch (\Exception $e) {
                        $validationErrors[] = 'End date invalid.';
                    }
                }
            } else {
                $row['end_date'] = null;
            }

            $row['validation_errors'] = $validationErrors;

            $dataToImport[] = $row;
        }

        return $dataToImport;
    }

    public function resolvedData($data, $companyId): array
    {
        foreach ($data as $index => $row) {

            $employee = Employee::query()
                ->where('company_id', $companyId)
                ->where('number', $row['employee_number'])
                ->first();

            $create = [
                'status' => EmploymentStatus::ACTIVE,
                'employment_type' => EmploymentType::{$row['employment_type']},
                'start_date' => Carbon::parse($row['start_date'])->toDateString(),
            ];

            if (!empty($row['end_of_service_type'])) {
                $create['end_of_service_type'] = EndOfServiceType::{$row['end_of_service_type']};
                $create['end_date'] = Carbon::parse($row['end_date'])->toDateString();
            }

            $employee->employmentProfiles()->create($create);
        }

        return array_map(function ($row) {return $row['id'];}, $data);
    }
}
