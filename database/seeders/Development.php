<?php

namespace Database\Seeders;

use App\Enums\CompanyUserAssignmentType;
use App\Enums\Compensation;
use App\Enums\Deduction;
use App\Enums\Formulable;
use App\Enums\Gender;
use App\Enums\IncomeTax;
use App\Enums\MaritalStatus;
use App\Enums\TimePeriodType;
use App\Models\Account;
use App\Models\Employee;
use App\Models\Formula;
use App\Models\Prototype;
use App\Models\TimePeriodPreset;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class Development extends Seeder
{
    use WithoutModelEvents;

    public static $salaryStatementModules = [
        [
            'formulable_type' => Formulable::EARNINGS,
            'order' => 1,
            'name' => 'Assigned Compensations',
            'reference' => 'employee_compensation',
            'conditions' => [
                [
                    'subject' => 'assignable',
                    'operator' => '=',
                    'value' => '1',
                ]
            ]
        ],[
            'formulable_type' => Formulable::DEDUCTIONS,
            'order' => 2,
            'name' => 'Assigned Deductions',
            'reference' => 'employee_deduction',
            'conditions' => [
                [
                    'subject' => 'assignable',
                    'operator' => '=',
                    'value' => '1',
                ]
            ]
        ],[
            'formulable_type' => Formulable::TAXABLE_INCOME,
            'order' => 3,
            'name' => 'Taxable Income',
            'reference' => null,
            'conditions' => null
        ],[
            'formulable_type' => Formulable::NONTAXABLE_INCOME,
            'order' => 4,
            'name' => 'Non-Taxable Income',
            'reference' => null,
            'conditions' => null
        ],[
            'formulable_type' => Formulable::INCOME_TAX,
            'order' => 5,
            'name' => 'Assigned Income Taxes',
            'reference' => 'employee_income_tax',
            'conditions' => [
                [
                    'subject' => 'assignable',
                    'operator' => '=',
                    'value' => '1',
                ]
            ]
        ],[
            'formulable_type' => Formulable::NET_INCOME,
            'order' => 6,
            'name' => 'Net Income',
            'reference' => null,
            'conditions' => null
        ],
    ];

    public static $timePeriodPresets = [
        [
            'type' => TimePeriodType::THIRTEENTH_MONTH,
            'name' => 'november_2nd',
            'readable_name' => 'November 2nd',
            'yearly_period' => [
                [
                    'key' => 'start_date',
                    'label' => 'Start Date',
                    'order' => 1,
                    'type' => 'date',
                    'readable' => 'November 02 of last year',
                    'value' => [
                        'base' => 'Nov 02 last year',
                        'year' => null,
                        'month' => null,
                        'day' => null,
                        'time' => 'startOfDay'
                    ]
                ],[
                    'key' => 'end_date',
                    'label' => 'End Date',
                    'order' => 2,
                    'type' => 'date',
                    'readable' => 'November 01 of current year',
                    'value' => [
                        'base' => 'Nov 01',
                        'year' => null,
                        'month' => null,
                        'day' => null,
                        'time' => 'endOfDay'
                    ]
                ]
            ]
        ], [
            'type' => TimePeriodType::PAY_PERIOD,
            'name' => 'end_of_month_cut_off',
            'readable_name' => 'Cut-off of End of month',
            'monthly_period' => [
                [
                    'key' => 'start_date',
                    'label' => 'Start Date',
                    'order' => 1,
                    'type' => 'date',
                    'readable' => '01 of month',
                    'value' => [
                        'base' => 'now',
                        'year' => null,
                        'month' => null,
                        'day' => 'startOfMonth',
                        'time' => 'startOfDay'
                    ]
                ],[
                    'key' => 'end_date',
                    'label' => 'End Date',
                    'order' => 2,
                    'type' => 'date',
                    'readable' => 'End of month',
                    'value' => [
                        'base' => 'now',
                        'year' => null,
                        'month' => null,
                        'day' => 'endOfMonth',
                        'time' => 'endOfDay'
                    ]
                ]
            ],
            'semimonthly_period' => [
                [
                    'key' => '1st_half_start_date',
                    'label' => '1st Half Start Date',
                    'order' => 1,
                    'type' => 'date',
                    'readable' => '01 of month',
                    'value' => [
                        'base' => 'now',
                        'year' => null,
                        'month' => null,
                        'day' => 'startOfMonth',
                        'time' => 'startOfDay'
                    ]
                ],[
                    'key' => '1st_half_end_date',
                    'label' => '1st Half End Date',
                    'order' => 2,
                    'type' => 'date',
                    'readable' => '15 of month',
                    'value' => [
                        'base' => 'now',
                        'year' => null,
                        'month' => null,
                        'day' => 15,
                        'time' => 'endOfDay'
                    ]
                ],[
                    'key' => '2nd_half_start_date',
                    'label' => '2nd Half Start Date',
                    'order' => 3,
                    'type' => 'date',
                    'readable' => '16 of month',
                    'value' => [
                        'base' => 'now',
                        'year' => null,
                        'month' => null,
                        'day' => 16,
                        'time' => 'startOfDay'
                    ]
                ],[
                    'key' => '2nd_half_end_date',
                    'label' => '2nd Half End Date',
                    'order' => 4,
                    'type' => 'date',
                    'readable' => 'End of month',
                    'value' => [
                        'base' => 'now',
                        'year' => null,
                        'month' => null,
                        'day' => 'endOfMonth',
                        'time' => 'endOfDay'
                    ]
                ],
            ]
        ], [
            'type' => TimePeriodType::PAY_PERIOD,
            'name' => '10th_cut_off',
            'readable_name' => 'Cut-off of 10th',
            'monthly_period' => [
                [
                    'key' => 'start_date',
                    'label' => 'Start Date',
                    'order' => 1,
                    'type' => 'date',
                    'readable' => '11 of current month',
                    'value' => [
                        'base' => 'now',
                        'year' => null,
                        'month' => null,
                        'day' => 11,
                        'time' => 'startOfDay'
                    ]
                ],[
                    'key' => 'end_date',
                    'label' => 'End Date',
                    'order' => 2,
                    'type' => 'date',
                    'readable' => '10 of next month',
                    'value' => [
                        'base' => 'now',
                        'year' => null,
                        'month' => 'addMonth.1',
                        'day' => 10,
                        'time' => 'endOfDay'
                    ]
                ]
            ],
            'semimonthly_period' => [
                [
                    'key' => '1st_half_start_date',
                    'label' => '1st Half Start Date',
                    'order' => 1,
                    'type' => 'date',
                    'readable' => '11 of current month',
                    'value' => [
                        'base' => 'now',
                        'year' => null,
                        'month' => null,
                        'day' => 11,
                        'time' => 'startOfDay'
                    ]
                ],[
                    'key' => '1st_half_end_date',
                    'label' => '1st Half End Date',
                    'order' => 2,
                    'type' => 'date',
                    'readable' => '25 of current month',
                    'value' => [
                        'base' => 'now',
                        'year' => null,
                        'month' => null,
                        'day' => 25,
                        'time' => 'endOfDay'
                    ]
                ],[
                    'key' => '2nd_half_start_date',
                    'label' => '2nd Half Start Date',
                    'order' => 3,
                    'type' => 'date',
                    'readable' => '26 of current month',
                    'value' => [
                        'base' => 'now',
                        'year' => null,
                        'month' => null,
                        'day' => 26,
                        'time' => 'startOfDay'
                    ]
                ],[
                    'key' => '2nd_half_end_date',
                    'label' => '2nd Half End Date',
                    'order' => 4,
                    'type' => 'date',
                    'readable' => '10 of next month',
                    'value' => [
                        'base' => 'now',
                        'year' => null,
                        'month' => 'addMonth.1',
                        'day' => 10,
                        'time' => 'endOfDay'
                    ]
                ],
            ]
        ], [
            'type' => TimePeriodType::PAY_PERIOD,
            'name' => '25th_cut_off',
            'readable_name' => 'Cut-off of 25th',
            'monthly_period' => [
                [
                    'key' => 'start_date',
                    'label' => 'Start Date',
                    'order' => 1,
                    'type' => 'date',
                    'readable' => '26 of last month',
                    'value' => [
                        'base' => 'now',
                        'year' => null,
                        'month' => 'subMonth.1',
                        'day' => 26,
                        'time' => 'startOfDay'
                    ]
                ],[
                    'key' => 'end_date',
                    'label' => 'End Date',
                    'order' => 2,
                    'type' => 'date',
                    'readable' => '25 of current month',
                    'value' => [
                        'base' => 'now',
                        'year' => null,
                        'month' => null,
                        'day' => 25,
                        'time' => 'endOfDay'
                    ]
                ]
            ],
            'semimonthly_period' => [
                [
                    'key' => '1st_half_start_date',
                    'label' => '1st Half Start Date',
                    'order' => 1,
                    'type' => 'date',
                    'readable' => '26 of last month',
                    'value' => [
                        'base' => 'now',
                        'year' => null,
                        'month' => 'subMonth.1',
                        'day' => 26,
                        'time' => 'startOfDay'
                    ]
                ],[
                    'key' => '1st_half_end_date',
                    'label' => '1st Half End Date',
                    'order' => 2,
                    'type' => 'date',
                    'readable' => '10 of current month',
                    'value' => [
                        'base' => 'now',
                        'year' => null,
                        'month' => null,
                        'day' => 10,
                        'time' => 'endOfDay'
                    ]
                ],[
                    'key' => '2nd_half_start_date',
                    'label' => '2nd Half Start Date',
                    'order' => 3,
                    'type' => 'date',
                    'readable' => '11 of current month',
                    'value' => [
                        'base' => 'now',
                        'year' => null,
                        'month' => null,
                        'day' => 11,
                        'time' => 'startOfDay'
                    ]
                ],[
                    'key' => '2nd_half_end_date',
                    'label' => '2nd Half End Date',
                    'order' => 4,
                    'type' => 'date',
                    'readable' => '25 of current month',
                    'value' => [
                        'base' => 'now',
                        'year' => null,
                        'month' => null,
                        'day' => 25,
                        'time' => 'endOfDay'
                    ]
                ],
            ]
        ]
    ];

    public function run(): void
    {
        Prototype::factory()->count(500)->create();

        //TimePeriod Presets
        foreach (self::$timePeriodPresets as $timePeriodPreset) {
            TimePeriodPreset::create($timePeriodPreset);
        }

        //Yearly Period Preset of Nov 2nd
        $november2ndThirteenMonthPeriodPreset = collect(self::$timePeriodPresets)
            ->where('name', 'november_2nd')
            ->where('type', TimePeriodType::THIRTEENTH_MONTH)
            ->first();

        $endOfMonthCutOffPeriodPreset = collect(self::$timePeriodPresets)
            ->where('type', TimePeriodType::PAY_PERIOD)
            ->where('name', 'end_of_month_cut_off')
            ->first();

        $twentyFifthCutOffPeriodPreset = collect(self::$timePeriodPresets)
            ->where('type', TimePeriodType::PAY_PERIOD)
            ->where('name', '25th_cut_off')
            ->first();

        //13th Month Formula Preset
        $thirteenMonthFormulaPreset = ['name' => 'Standard-13th-Month', 'formulable_type' => Formulable::EARNINGS  ,'component_type' => Compensation::BENEFIT, 'interpolation' => false,
            'default_settings' => $november2ndThirteenMonthPeriodPreset['yearly_period']
        ];

        //Formula Presets
        $formulaPresets = [
            ['name' => 'Standard-Salary', 'formulable_type' => Formulable::EARNINGS ,'component_type' => Compensation::SALARY, 'interpolation' => false,
                'default_settings' => [
                    [
                        'key' => 'regular_rate',
                        'label' => 'Regular Rate',
                        'order' => 1,
                        'type' => 'number',
                        'readable' => '100%',
                        'value' => '1'
                    ]
                ]
            ],
            ['name' => 'Standard-Meal', 'formulable_type' => Formulable::EARNINGS ,'component_type' => Compensation::REGULAR_ALLOWANCE, 'interpolation' => false],
            ['name' => 'Standard-Overtime', 'formulable_type' => Formulable::EARNINGS ,'component_type' => Compensation::OVERTIME, 'interpolation' => false,
                'default_settings' => [
                    [
                        'key' => 'regular_overtime_rate',
                        'label' => 'Regular Overtime Rate',
                        'order' => 1,
                        'type' => 'number',
                        'readable' => '125%',
                        'value' => '1.25'
                    ]
                ]
            ],
            //...[$thirteenMonth],
            ['name' => 'Standard-Tardiness', 'formulable_type' => Formulable::DEDUCTIONS  ,'component_type' => Deduction::DEDUCTION, 'interpolation' => false],
            ['name' => 'Standard-Absent', 'formulable_type' => Formulable::DEDUCTIONS  ,'component_type' => Deduction::DEDUCTION, 'interpolation' => false],
            ['name' => 'Standard-SSS-Employed-Contribution', 'formulable_type' => Formulable::DEDUCTIONS  ,'component_type' => Deduction::CONTRIBUTION, 'interpolation' => false],
            ['name' => 'Standard-Philhealth-Contribution', 'formulable_type' => Formulable::DEDUCTIONS  ,'component_type' => Deduction::CONTRIBUTION, 'interpolation' => false],
            ['name' => 'Standard-Pagibig-Contribution', 'formulable_type' => Formulable::DEDUCTIONS ,'component_type' => Deduction::CONTRIBUTION, 'interpolation' => false],
            ['name' => 'Standard-Taxable-Income', 'formulable_type' => Formulable::TAXABLE_INCOME ,'component_type' => null, 'interpolation' => true],
            ['name' => 'Standard-Nontaxable-Income', 'formulable_type' => Formulable::NONTAXABLE_INCOME ,'component_type' => null, 'interpolation' => true],
            ['name' => 'Standard-Withholding-Tax', 'formulable_type' => Formulable::INCOME_TAX ,'component_type' => IncomeTax::WITHHOLDING_TAX, 'interpolation' => false],
            ['name' => 'Standard-Net-Income', 'formulable_type' => Formulable::NET_INCOME ,'component_type' => null, 'interpolation' => true]
        ];

        foreach ($formulaPresets as $formula) {
            Formula::create($formula);
        }

        /**************************************************************************************************************************************************************************************************************/

        //Superadmin
        $superAdmin = User::factory()->superAdmin()->create(['name' => 'kim.123', 'email' => 'luxere20@gmail.com', 'timezone' => 'Asia/Manila']);

        //Account 1001
        $account1001 = Account::factory()->standard()->create(['number' => 'ACC-1001']);
        //Account 1002
        $account1002 = Account::factory()->standard()->create(['number' => 'ACC-1002']);

        //Account 1001 Companies
        $company1001A = $account1001->companies()->create(['name' => 'Company 1001-A', 'code' => '1001-A', 'timezone' => 'Asia/Manila']);

        //Account 1002 Companies
        $company1002A = $account1002->companies()->create(['name' => 'Company 1002-A', 'code' => '1002-A', 'timezone' => 'Asia/Manila']);
        $company1002B = $account1002->companies()->create(['name' => 'Company 1002-B', 'code' => '1002-B', 'timezone' => 'Asia/Manila']);
        $company1002C = $account1002->companies()->create(['name' => 'Company 1002-C', 'code' => '1002-C', 'timezone' => 'Asia/Manila']);

        //Assign 1001, 1002 Companies to Superadmin as Admin
        $superAdmin->companies()->syncWithoutDetaching([
            $company1001A->id => ['assignment_type' => CompanyUserAssignmentType::ADMIN],
            $company1002A->id => ['assignment_type' => CompanyUserAssignmentType::ADMIN],
            $company1002B->id => ['assignment_type' => CompanyUserAssignmentType::ADMIN],
            $company1002C->id => ['assignment_type' => CompanyUserAssignmentType::ADMIN],
        ]);

        //Account 1002User01
        $account1002User01 = User::factory()->default()->create(['name' => '1002.user.1', 'email' => 'luxere20@gmail.com']);
        $account1002User02 = User::factory()->default()->create(['name' => '1002.user.2', 'email' => 'luxere20@gmail.com']);

        /*
         * Employee: has employee info and default assigned to a company
         * Employee Admin: has employee info and admin assigned to a company
         * Admin: no employee info and admin assigned to a company
         * */

        //Assign 1002User01 to Company 1002-A as Employee
        $account1002User01->companies()->syncWithoutDetaching([$company1002A->id => ['assignment_type' => CompanyUserAssignmentType::DEFAULT]]);


        //Assign 1002User01 to Company 1002-B as Admin
        $account1002User01->companies()->syncWithoutDetaching([$company1002B->id => ['assignment_type' => CompanyUserAssignmentType::ADMIN]]);

        //Assign 1002User02 to Company 1002-B as Employee
        $account1002User02->companies()->syncWithoutDetaching([$company1002B->id => ['assignment_type' => CompanyUserAssignmentType::DEFAULT]]);


        //Assign 1002User01 to Company 1002-C as Employee Admin
        $account1002User01->companies()->syncWithoutDetaching([$company1002C->id => ['assignment_type' => CompanyUserAssignmentType::ADMIN]]);

        //Assign 1002User02 to Company 1002-C as Employee
        $account1002User02->companies()->syncWithoutDetaching([$company1002C->id => ['assignment_type' => CompanyUserAssignmentType::DEFAULT]]);

        /**************************************************************************************************************************************************************************************************************/

        //Company 1002-B, 1002-C Salary Statement Modules
        foreach (self::$salaryStatementModules as $salaryStatementModule) {
            $company1002B->salaryStatementModules()->create($salaryStatementModule);
            $company1002C->salaryStatementModules()->create($salaryStatementModule);
        }

        $formulas = Formula::all();

        //Company 1002-A, 1002-B, 1002-C Assign Formula Presets
        foreach ($formulas as $formula) {
            $company1002A->formulas()->syncWithoutDetaching([$formula->id => ['settings' => isset($formula->default_settings->cast) ? json_encode($formula->default_settings->cast) : null]]);
            $company1002B->formulas()->syncWithoutDetaching([$formula->id => ['settings' => isset($formula->default_settings->cast) ? json_encode($formula->default_settings->cast) : null]]);
            $company1002C->formulas()->syncWithoutDetaching([$formula->id => ['settings' => isset($formula->default_settings->cast) ? json_encode($formula->default_settings->cast) : null]]);
        }

        //Company 1002-B Pay Period Preset of End of Month Cut-off
        $company1002B->payPeriodSetting()->create([
            'days_to_pay_after_cut_off' => 5,
            'time_period_preset_reference' => $endOfMonthCutOffPeriodPreset['name'],
            'monthly_pay_period' => $endOfMonthCutOffPeriodPreset['monthly_period'],
            'semimonthly_pay_period' => $endOfMonthCutOffPeriodPreset['semimonthly_period'],
        ]);

        //Company 1002-C Pay Period Preset of 25th Cut-off
        $company1002C->payPeriodSetting()->create([
            'days_to_pay_after_cut_off' => 5,
            'time_period_preset_reference' => $twentyFifthCutOffPeriodPreset['name'],
            'monthly_pay_period' => $twentyFifthCutOffPeriodPreset['monthly_period'],
            'semimonthly_pay_period' => $twentyFifthCutOffPeriodPreset['semimonthly_period'],
        ]);

        //Company 1002-B, 1002-C Pre-create Compensations
        $compensationsPresets = [
            ['name' => 'Basic Salary', 'assignable' => true, 'type' => Compensation::SALARY, 'formula' => 'Standard-Salary'],
            ['name' => 'Meal Allowance', 'assignable' => true, 'type' => Compensation::REGULAR_ALLOWANCE, 'formula' => 'Standard-Meal'],
            ['name' => 'Overtime', 'assignable' => true, 'type' => Compensation::OVERTIME, 'formula' => 'Standard-Overtime'],
        ];

        foreach ($compensationsPresets as $index => $compensationPreset) {
            $this->createPayrollComponent($company1002B, $index, Formulable::EARNINGS, 'compensations', $compensationPreset);
            $this->createPayrollComponent($company1002C, $index, Formulable::EARNINGS, 'compensations', $compensationPreset);
        }

        //Company 1002-B, 1002-C Pre-create Deductions
        $deductionsPresets = [
            ['name' => 'Tardiness', 'assignable' => true, 'type' => Deduction::DEDUCTION, 'formula' => 'Standard-Tardiness'],
            ['name' => 'Absent', 'assignable' => true, 'type' => Deduction::DEDUCTION, 'formula' => 'Standard-Absent'],
            ['name' => 'SSS-Employed', 'assignable' => true, 'type' => Deduction::CONTRIBUTION, 'formula' => 'Standard-SSS-Employed-Contribution'],
        ];

        foreach ($deductionsPresets as $index => $deductionsPreset) {
            $this->createPayrollComponent($company1002B, $index, Formulable::DEDUCTIONS, 'deductions', $deductionsPreset);
            $this->createPayrollComponent($company1002C, $index, Formulable::DEDUCTIONS, 'deductions', $deductionsPreset);
        }

        //Company 1002-B, 1002-C Pre-create Income Taxes
        $incomeTaxesPresets = [
            ['name' => 'Income Tax', 'assignable' => true, 'type' => IncomeTax::WITHHOLDING_TAX, 'formula' => 'Standard-Withholding-Tax'],
        ];

        foreach ($incomeTaxesPresets as $index => $incomeTaxesPreset) {
            $this->createPayrollComponent($company1002B, $index, Formulable::INCOME_TAX, 'incomeTaxes', $incomeTaxesPreset);
            $this->createPayrollComponent($company1002C, $index, Formulable::INCOME_TAX, 'incomeTaxes', $incomeTaxesPreset);
        }

        /**************************************************************************************************************************************************************************************************************/

        //Create Employee Info 1002User01 to Company 1002-A
        $account1002User01->employees()->create([
            'ulid' => Str::ulid(),
            'company_id' => $company1002A->id,
            'number' => 'A1001',
            'given_name' => 'Employee 01',
            'middle_name' => 'A',
            'family_name' => '1002',
            'birth_date' => '1990-01-01',
            'gender' => Gender::FEMALE,
            'marital_status' => MaritalStatus::SINGLE,
        ]);

        //Create Employee Info 1002User01 to Company 1002-C
        $account1002User01->employees()->create([
            'ulid' => Str::ulid(),
            'company_id' => $company1002C->id,
            'number' => 'C1001',
            'given_name' => 'Employee 01',
            'middle_name' => 'C',
            'family_name' => '1002',
            'birth_date' => '1990-01-01',
            'gender' => Gender::FEMALE,
            'marital_status' => MaritalStatus::SINGLE,
        ]);

        //Create Employee Info 1002User01 to Company 1002-B
        $account1002User02->employees()->create([
            'ulid' => Str::ulid(),
            'company_id' => $company1002B->id,
            'number' => 'B1001',
            'given_name' => 'Employee 01',
            'middle_name' => 'B',
            'family_name' => '1002',
            'birth_date' => '1990-01-01',
            'gender' => Gender::FEMALE,
            'marital_status' => MaritalStatus::SINGLE,
        ]);

        //Create Employee Info 1002User02 to Company 1002-C
        $account1002User02->employees()->create([
            'ulid' => Str::ulid(),
            'company_id' => $company1002C->id,
            'number' => 'C1002',
            'given_name' => 'Employee 02',
            'middle_name' => 'C',
            'family_name' => '1002',
            'birth_date' => '1990-01-01',
            'gender' => Gender::FEMALE,
            'marital_status' => MaritalStatus::SINGLE,
        ]);
    }

    public function createPayrollComponent(Model $company, $index, $formulableType, $component, $attributes): void
    {
        $formulas = $company->formulas;

        $company->{$component}()->create([
            ...collect($attributes)->except('formula')->toArray(),
            'order' => ++$index,
            'company_formula_id' => $formulas->where('formulable_type', $formulableType)
                ->where('component_type', $attributes['type'])
                ->where('name', $attributes['formula'])
                ->first()->pivot->id,
        ]);
    }
}
