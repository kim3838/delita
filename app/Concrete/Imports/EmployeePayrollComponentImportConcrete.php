<?php

namespace App\Concrete\Imports;

use App\Blueprint\Imports\EmployeePayrollComponentImport;
use App\Blueprint\Repositories\EmployeePayrollComponentRepository;
use App\Concrete\BaseImportConcrete;
use App\Enums\Compensation as CompensationEnum;
use App\Enums\PayFrequency as PayFrequencyEnum;
use App\Enums\PayPeriod;
use App\Enums\PayType;
use App\Exports\BlankEmployeePayItemsTemplateExport;
use App\Http\Requests\PolymorphicEmployeePayrollComponent\BasePolymorphicEmployeePayrollComponentRequest;
use App\Models\Company;
use App\Models\Compensation;
use App\Models\Deduction;
use App\Models\Employee;
use App\Models\EmployeePayrollComponent;
use App\Models\IncomeTax;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;

class EmployeePayrollComponentImportConcrete extends BaseImportConcrete implements EmployeePayrollComponentImport
{
    public function model(): string
    {
        return EmployeePayrollComponent::class;
    }

    public function exportTemplate(): string
    {
        return BlankEmployeePayItemsTemplateExport::class;
    }

    public function validateData($data, $companyId): array
    {
        $dataToImport = [];

        foreach ($data as $index => $row) {

            $validationErrors = [];

            $payrollComponent = null;
            $payrollComponentIsAmountable = false;
            $payPeriod = null;
            $payFrequency = null;

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

                    $payFrequency = $employee->payFrequency;
                }
            }

            if (empty($row['payroll_component_code'])) {
                $validationErrors[] = 'Payroll component code is required.';
            } else {

                $payrollComponent = Compensation::query()
                    ->where('company_id', $companyId)
                    ->where('code', $row['payroll_component_code'])
                    ->where('assignable', true)
                    ->first();

                if(empty($payrollComponent)){

                    $payrollComponent = Deduction::query()
                        ->where('company_id', $companyId)
                        ->where('code', $row['payroll_component_code'])
                        ->where('assignable', true)
                        ->first();
                }

                if(empty($payrollComponent)){

                    $payrollComponent = IncomeTax::query()
                        ->where('company_id', $companyId)
                        ->where('code', $row['payroll_component_code'])
                        ->where('assignable', true)
                        ->first();
                }

                if(empty($payrollComponent)){
                    $validationErrors[] = 'Payroll component not found.';

                    $this->resolveValidatedRow($row, $validationErrors, $dataToImport);
                    continue;
                } else {

                    $row['payroll_componentable_id'] = $payrollComponent->id;
                    $row['payroll_componentable_type'] = Relation::getMorphAlias(get_class($payrollComponent));
                    $row['formulable_type'] = $payrollComponent->formulable_type;
                }
            }

            if(!empty($payrollComponent)){

                $payrollComponentType = $payrollComponent->type;

                $payrollComponentIsAmountable = in_array($payrollComponentType, [
                    CompensationEnum::BASIC_PAY,
                    CompensationEnum::REGULAR_ALLOWANCE
                ]);
            }

            if(!$this->isActionAuthorized('create', $this->model)){

                $validationErrors[] = 'Unauthorized create.';

                $this->resolveValidatedRow($row, $validationErrors, $dataToImport);
                continue;
            }

            $row['payroll_component_is_amountable'] = $payrollComponentIsAmountable;

            if($payrollComponentIsAmountable){

                if (empty($row['amount'])) {
                    $validationErrors[] = 'Amount is required.';
                } else {

                    $amountValidation = Validator::make($row,[
                        'amount' => new BasePolymorphicEmployeePayrollComponentRequest()->rules()['amount'],
                    ]);

                    if ($amountValidation->fails()) {
                        $validationErrors[] = $amountValidation->errors()->first();
                    }
                }

                if (empty($row['pay_period'])) {
                    $validationErrors[] = 'Pay period is required.';
                } else {

                    $payPeriodValid = isNameInEnum(PayPeriod::class, $row['pay_period']);

                    if(!$payPeriodValid){
                        $validationErrors[] = 'Pay period invalid.';
                    } else {
                        $payPeriod = PayPeriod::{$row['pay_period']};
                    }
                }

                $payTypeEnabled = false;

                if($payTypeEnabled){
                    if (empty($row['pay_type'])) {
                        $validationErrors[] = 'Pay type is required.';
                    } else {

                        $payTypeValid = isNameInEnum(PayType::class, $row['pay_type']);

                        if(!$payTypeValid){
                            $validationErrors[] = 'Pay type invalid.';
                        }
                    }
                }

                if(!empty($payPeriod) && !empty($payFrequency)){

                    $payPeriodIsSemimonthlyOrMonthly = in_array($payPeriod, [
                        PayPeriod::SEMIMONTHLY,
                        PayPeriod::MONTHLY
                    ]);

                    $employeePayFrequencyIsDailyOrWeekly = in_array($payFrequency->type, [
                        PayFrequencyEnum::WEEKLY
                    ]);

                    if($payPeriodIsSemimonthlyOrMonthly && $employeePayFrequencyIsDailyOrWeekly){
                        $validationErrors[] = 'Semi-monthly/Monthly pay period are not allowed for Weekly frequencies.';
                    }
                }
            } else {

                $row['amount'] = null;
                $row['pay_period'] = null;
                $row['pay_type'] = null;
                $row['pay_frequency'] = null;
            }

            $this->resolveValidatedRow($row, $validationErrors, $dataToImport);
        }

        return $dataToImport;
    }

    public function resolvedData($data, $companyId): array
    {
        $company = Company::query()->find($companyId);

        foreach ($data as $index => $row) {

            $employee = Employee::query()
                ->where('company_id', $companyId)
                ->where('number', $row['employee_number'])
                ->first();

            $create = [
                'employee_id' => $employee->id,
                'payroll_componentable_id' => $row['payroll_componentable_id'],
                'payroll_componentable_type' => $row['payroll_componentable_type'],
                'formulable_type' => $row['formulable_type'],
                ...($row['payroll_component_is_amountable'] ? [
                    'amount' => $row['amount'],
                    'currency' => $company->currency,
                    'pay_period' => PayPeriod::{$row['pay_period']},
                    'pay_type' => PayType::BY_ATTENDANCE,
                ] : [])
            ];

            App::make(EmployeePayrollComponentRepository::class)->model()::create($create);
        }

        return array_map(function ($row) {return $row['id'];}, $data);
    }
}
