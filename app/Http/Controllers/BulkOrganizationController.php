<?php

namespace App\Http\Controllers;

use App\Blueprint\EnumInterface;
use App\Blueprint\Repositories\CompensationRepository;
use App\Blueprint\Repositories\DeductionRepository;
use App\Blueprint\Repositories\DepartmentRepository;
use App\Blueprint\Repositories\DesignationRepository;
use App\Blueprint\Repositories\EmployeeGroupRepository;
use App\Blueprint\Repositories\IncomeTaxRepository;
use App\Blueprint\Repositories\PayFrequencyRepository;
use App\Enums\Compensation as CompensationEnum;
use App\Enums\PayFrequency;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Transformers\Compensation\SelectionAsComponentSubTypeTransformer as CompensationSelectionAsComponentSubTypeTransformer;
use App\Transformers\Deduction\SelectionAsComponentSubTypeTransformer as DeductionSelectionAsComponentSubTypeTransformer;
use App\Transformers\Department\SelectionTransformer as DepartmentSelectionTransformer;
use App\Transformers\Designation\SelectionTransformer as DesignationSelectionTransformer;
use App\Transformers\EmployeeGroup\SelectionTransformer as EmployeeGroupSelectionTransformer;
use App\Transformers\IncomeTax\SelectionAsComponentSubTypeTransformer as IncomeTaxSelectionAsComponentSubTypeTransformer;
use App\Transformers\PayFrequency\SelectionTransformer as PayFrequencySelectionTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class BulkOrganizationController extends Controller
{
    public function index(Request $request)
    {
        if($request->expectsJson()){

            $filters = json_decode($request->get('filters'));

            $employeeGroupSelection = [];
            $payFrequencySelection = [];
            $departmentSelection = [];
            $designationSelection = [];

            $compensationNames = [];
            $deductionNames = [];
            $incomeTaxNames = [];

            if(isset($filters->company_id)){

                $employeeGroupSelection = Fractal::collection(App::make(EmployeeGroupRepository::class)->selection($filters), EmployeeGroupSelectionTransformer::class)['data'];
                $departmentSelection = Fractal::collection(App::make(DepartmentRepository::class)->selection($filters), DepartmentSelectionTransformer::class)['data'];
                $designationSelection = Fractal::collection(App::make(DesignationRepository::class)->selection($filters), DesignationSelectionTransformer::class)['data'];

                $payFrequencyFilters = $filters;
                $payFrequencyFilters->frequency_types = [PayFrequency::MONTHLY->value, PayFrequency::SEMIMONTHLY->value];
                $payFrequencySelection = Fractal::collection(App::make(PayFrequencyRepository::class)
                    ->selection($payFrequencyFilters), PayFrequencySelectionTransformer::class)['data'];

                $compensationNames = Fractal::collection(App::make(CompensationRepository::class)->selection($filters), CompensationSelectionAsComponentSubTypeTransformer::class)['data'];
                $deductionNames = Fractal::collection(App::make(DeductionRepository::class)->selection($filters), DeductionSelectionAsComponentSubTypeTransformer::class)['data'];
                $incomeTaxNames = Fractal::collection(App::make(IncomeTaxRepository::class)->selection($filters), IncomeTaxSelectionAsComponentSubTypeTransformer::class)['data'];
            }

            $compensations = App::make(EnumInterface::class)->selection('compensation');
            $deductions = App::make(EnumInterface::class)->selection('deduction');
            $incomeTaxes = App::make(EnumInterface::class)->selection('income_tax');

            $assignableCompensations = collect($compensations::all())->filter(function($compensation){
               return !in_array($compensation['value'], [CompensationEnum::LEAVE_PAY->value, CompensationEnum::HOLIDAY_PAY->value]);
            })->values()->toArray();

            return ResponseJson::successfulResponse([
                'employee_groups' => $employeeGroupSelection,
                'departments' => $departmentSelection,
                'designations' => $designationSelection,
                'pay_frequencies' => $payFrequencySelection,
                'payroll_component' => [
                    'type' => array_merge($assignableCompensations, $deductions::all(), $incomeTaxes::all()),
                    'names' => array_merge($compensationNames, $deductionNames, $incomeTaxNames)
                ]
            ]);
        }

        abort(404);
    }
}
