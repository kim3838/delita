<?php

namespace App\Concrete\Imports;

use App\Blueprint\Imports\EmployeeIdentificationImport;
use App\Blueprint\Repositories\EmployeeIdentificationRepository;
use App\Concrete\BaseImportConcrete;
use App\Enums\IdentificationType;
use App\Exports\BlankEmployeeIdentificationTemplateExport;
use App\Models\Employee;
use App\Models\EmployeeIdentification;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\App;

class EmployeeIdentificationImportConcrete extends BaseImportConcrete implements EmployeeIdentificationImport
{
    public function model(): string
    {
        return EmployeeIdentification::class;
    }

    public function exportTemplate(): string
    {
        return BlankEmployeeIdentificationTemplateExport::class;
    }

    /**
     * @throws BindingResolutionException
     */
    public function validateData($data, $companyId):array
    {
        $repository = App::make(EmployeeIdentificationRepository::class);
        $dataToImport = [];
        $employeeIdentificationTypePairs = [];
        $employee = null;

        foreach ($data as $index => $row) {

            $validationErrors = [];

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
                } else {
                    $row['employee_id'] = $employee->id;
                }
            }

            if(!$this->isActionAuthorized('create', $this->model)){

                $validationErrors[] = 'Unauthorized create.';

                $this->resolveValidatedRow($row, $validationErrors, $dataToImport);
                continue;
            }

            if (empty($row['identification_type'])) {
                $validationErrors[] = 'Identification type is required.';
            } else {

                $employmentTypeValid = isValueInEnum(IdentificationType::class, $row['identification_type']);

                if(!$employmentTypeValid){
                    $validationErrors[] = 'Identification type invalid.';

                    $this->resolveValidatedRow($row, $validationErrors, $dataToImport);
                    continue;
                }

                $existingIdentification = $repository->model()::query()
                    ->where('employee_id', $employee->id)
                    ->where('type', $row['identification_type'])
                    ->first();

                if(!empty($existingIdentification)){
                    $validationErrors[] = 'Employee identification already exist.';

                    $this->resolveValidatedRow($row, $validationErrors, $dataToImport);
                    continue;
                }
            }

            $employeeIdentificationTypePairsCollection = collect($employeeIdentificationTypePairs);
            $duplicate = $employeeIdentificationTypePairsCollection->filter(function ($identificationTypePair) use ($row) {
                return $identificationTypePair['employee_number'] == $row['employee_number'] &&
                    $identificationTypePair['identification_type'] == $row['identification_type'];
            });

            if($duplicate->count() > 0){
                $validationErrors[] = 'Identification duplicate from file.';
                $this->resolveValidatedRow($row, $validationErrors, $dataToImport);

                continue;
            } else {
                $employeeIdentificationTypePairs[] = [
                    'employee_number' => $row['employee_number'],
                    'identification_type' => $row['identification_type'],
                ];
            }

            if (empty($row['number'])) {
                $validationErrors[] = 'Number is required.';

                $this->resolveValidatedRow($row, $validationErrors, $dataToImport);
                continue;
            }

            $this->resolveValidatedRow($row, $validationErrors, $dataToImport);
        }

        return $dataToImport;
    }

    public function resolvedData($data, $companyId): array
    {
        $repository = App::make(EmployeeIdentificationRepository::class);

        foreach ($data as $index => $row) {

            $employee = Employee::query()
                ->where('company_id', $companyId)
                ->where('number', $row['employee_number'])
                ->first();

            $create = [
                'type' => $row['identification_type'],
                'number' => $row['number'],
                'readable_number' => $row['readable_number'],
            ];

            $existingIdentification = $repository->model()::query()
                ->where('employee_id', $employee->id)
                ->where('type', $row['identification_type'])
                ->first();

            if(empty($existingIdentification)){

                $employee->identifications()->create($create);
            }
        }

        return array_map(function ($row) {return $row['id'];}, $data);
    }
}
