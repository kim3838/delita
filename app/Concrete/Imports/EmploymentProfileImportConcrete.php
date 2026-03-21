<?php

namespace App\Concrete\Imports;

use App\Blueprint\Imports\EmploymentProfileImport;
use App\Concrete\BaseImportConcrete;
use App\Enums\EmploymentStatus;
use App\Enums\EmploymentType;
use App\Enums\EndOfServiceType;
use App\Exports\BlankEmploymentProfileTemplateExport;
use App\Http\Requests\EmploymentProfile\BaseStoreAndUpdateEmploymentProfileRequest;
use App\Models\Employee;
use App\Models\EmploymentProfile;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class EmploymentProfileImportConcrete extends BaseImportConcrete implements EmploymentProfileImport
{
    public function model(): string
    {
        return EmploymentProfile::class;
    }

    public function exportTemplate(): string
    {
        return BlankEmploymentProfileTemplateExport::class;
    }

    public function validateData($data, $companyId): array
    {
        $dataToImport = [];

        foreach ($data as $index => $row) {

            $validationErrors = [];

            if(!$this->isActionAuthorized('create', $this->model)){

                $validationErrors[] = 'Unauthorized create.';

                $this->resolveValidatedRow($row, $validationErrors, $dataToImport);
                continue;
            }

            if (empty($row['employee_number'])) {
                $validationErrors[] = 'Employee number is required.';
                $this->resolveValidatedRow($row, $validationErrors, $dataToImport);
                continue;
            } else {

                $employee = Employee::query()
                    ->where('company_id', $companyId)
                    ->where('number', $row['employee_number'])
                    ->first();

                if (empty($employee)) {
                    $validationErrors[] = 'Employee not found.';

                    $this->resolveValidatedRow($row, $validationErrors, $dataToImport);
                    continue;
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
                    $row['start_date'] = null;
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

                    $endDateValidation = Validator::make($row, [
                        'end_date' => new BaseStoreAndUpdateEmploymentProfileRequest()->rules()['end_date'],
                    ]);

                    $endDateValidation->setCustomMessages([
                        'end_date.after_or_equal' => 'End date must be after or equal to start date.',
                        'end_date.date_format' => 'End date invalid.',
                    ]);

                    if($endDateValidation->fails()){
                        $validationErrors[] = $endDateValidation->errors()->first();
                    }
                }
            } else {
                $row['end_date'] = null;
            }

            $this->resolveValidatedRow($row, $validationErrors, $dataToImport);
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
