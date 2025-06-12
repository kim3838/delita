<?php

namespace Database\Seeders;

use App\Enums\CompanyUserAssignmentType;
use App\Enums\Compensation;
use App\Enums\Deduction;
use App\Enums\Formulable;
use App\Enums\IncomeTax;
use App\Enums\TimePeriodType;
use App\Models\Account;
use App\Models\Company;
use App\Models\Formula;
use App\Models\Prototype;
use App\Models\TimePeriodPreset;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $superAdmin = User::factory()->superAdmin()
            ->create(['name' => 'kim.123', 'email' => 'luxere20@gmail.com', 'timezone' => 'Asia/Manila']);

        $defaultUser = User::factory()->default()
            ->create(['name' => 'user.123', 'email' => 'kimdeguzman20@yahoo.com']);

        User::factory(8)->create();

        //Create time period presets
        $timePeriodPresets = [
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
                        'readable' => '09 of last month',
                        'value' => [
                            'base' => 'now',
                            'year' => null,
                            'month' => 'subMonth.1',
                            'day' => 9,
                            'time' => 'startOfDay'
                        ]
                    ],[
                        'key' => 'end_date',
                        'label' => 'End Date',
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
                    ]
                ],
                'semimonthly_period' => [
                    [
                        'key' => '1st_half_start_date',
                        'label' => '1st Half Start Date',
                        'order' => 1,
                        'type' => 'date',
                        'readable' => '11 of last month',
                        'value' => [
                            'base' => 'now',
                            'year' => null,
                            'month' => 'subMonth.1',
                            'day' => 11,
                            'time' => 'startOfDay'
                        ]
                    ],[
                        'key' => '1st_half_end_date',
                        'label' => '1st Half End Date',
                        'order' => 2,
                        'type' => 'date',
                        'readable' => '25 of last month',
                        'value' => [
                            'base' => 'now',
                            'year' => null,
                            'month' => 'subMonth.1',
                            'day' => 25,
                            'time' => 'endOfDay'
                        ]
                    ],[
                        'key' => '2nd_half_start_date',
                        'label' => '2nd Half Start Date',
                        'order' => 3,
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
                        'key' => '2nd_half_end_date',
                        'label' => '2nd Half End Date',
                        'order' => 4,
                        'type' => 'date',
                        'readable' => '10 of current month',
                        'value' => [
                            'base' => 'now',
                            'year' => null,
                            'month' => null,
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

        foreach ($timePeriodPresets as $timePeriodPreset) {
            TimePeriodPreset::create($timePeriodPreset);
        }

        $accounts = Account::factory()->count(3)->has(Company::factory()->count(2))->create();

        $defaultUser->companies()->syncWithoutDetaching([1 => ['assignment_type' => CompanyUserAssignmentType::DEFAULT]]);
        $defaultUser->companies()->syncWithoutDetaching([2 => ['assignment_type' => CompanyUserAssignmentType::ADMIN]]);

        $november2ndThirteenMonthPeriodPreset = collect($timePeriodPresets)
            ->where('name', 'november_2nd')
            ->where('type', TimePeriodType::THIRTEENTH_MONTH)
            ->first();

        $formulas = [
            ['name' => 'Standard-Salary', 'formulable_type' => Formulable::EARNINGS ,'component_type' => Compensation::SALARY, 'interpolation' => false],
            ['name' => 'Standard-Meal', 'formulable_type' => Formulable::EARNINGS ,'component_type' => Compensation::REGULAR_ALLOWANCE, 'interpolation' => false],
            ['name' => 'Standard-Overtime', 'formulable_type' => Formulable::EARNINGS ,'component_type' => Compensation::OVERTIME, 'interpolation' => false],
            ['name' => 'Standard-13th-Month', 'formulable_type' => Formulable::EARNINGS  ,'component_type' => Compensation::BENEFIT, 'interpolation' => false,
                'default_settings' => $november2ndThirteenMonthPeriodPreset['yearly_period']
            ],
            ['name' => 'Standard-Tardiness', 'formulable_type' => Formulable::DEDUCTIONS  ,'component_type' => Deduction::DEDUCTION, 'interpolation' => false],
            ['name' => 'Standard-Absent', 'formulable_type' => Formulable::DEDUCTIONS  ,'component_type' => Deduction::DEDUCTION, 'interpolation' => false],
            ['name' => 'Standard-SSS-Employed-Contribution', 'formulable_type' => Formulable::DEDUCTIONS  ,'component_type' => Deduction::CONTRIBUTION, 'interpolation' => false],
            ['name' => 'Standard-Philhealth-Contribution', 'formulable_type' => Formulable::DEDUCTIONS  ,'component_type' => Deduction::CONTRIBUTION, 'interpolation' => false],
            ['name' => 'Standard-Pagibig-Contribution', 'formulable_type' => Formulable::DEDUCTIONS ,'component_type' => Deduction::CONTRIBUTION, 'interpolation' => false],
            ['name' => 'Standard-Taxable-Income', 'formulable_type' => Formulable::TAXABLE_INCOME ,'component_type' => null, 'interpolation' => true],
            ['name' => 'Standard-Nontaxable-Income', 'formulable_type' => Formulable::NON_TAXABLE_INCOME ,'component_type' => null, 'interpolation' => true],
            ['name' => 'Standard-Withholding-Tax', 'formulable_type' => Formulable::INCOME_TAX ,'component_type' => IncomeTax::WITHHOLDING_TAX, 'interpolation' => false],
            ['name' => 'Standard-Net-Income', 'formulable_type' => Formulable::NET_INCOME ,'component_type' => null, 'interpolation' => true]
        ];

        foreach ($formulas as $formula) {
            Formula::create($formula);
        }

        $formulas = Formula::all();

        foreach ($accounts as $account) {

            foreach ($account->companies as $company){
                $company->users()->syncWithoutDetaching([$superAdmin->id => ['assignment_type' => CompanyUserAssignmentType::ADMIN]]);
            }

            foreach ($account->companies as $company){
                foreach ($formulas as $formula) {
                    $company->formulas()->syncWithoutDetaching([$formula->id => ['settings' => isset($formula->default_settings->cast) ? json_encode($formula->default_settings->cast) : null]]);
                }
            }
        }

        $devSubjectCompany = Company::find(2);
        $devSubjectCompanyFormulas = $devSubjectCompany->formulas;

        //Create Compensation
        $devSubjectCompany->compensations()->create([
            'name' => 'Basic Salary',
            'order' => 1,
            'assignable' => true,
            'type' => Compensation::SALARY,
            'company_formula_id' => $devSubjectCompanyFormulas
                ->where('formulable_type', Formulable::EARNINGS)
                ->where('component_type', Compensation::SALARY)->first()->pivot->id,
        ]);

        $devSubjectCompany->compensations()->create([
            'name' => 'Meal Allowance',
            'order' => 2,
            'assignable' => true,
            'type' => Compensation::REGULAR_ALLOWANCE,
            'company_formula_id' => $devSubjectCompanyFormulas
                ->where('formulable_type', Formulable::EARNINGS)
                ->where('component_type', Compensation::REGULAR_ALLOWANCE)->first()->pivot->id,
        ]);

        $devSubjectCompany->compensations()->create([
            'name' => 'Overtime',
            'order' => 3,
            'assignable' => true,
            'type' => Compensation::OVERTIME,
            'company_formula_id' => $devSubjectCompanyFormulas
                ->where('formulable_type', Formulable::EARNINGS)
                ->where('component_type', Compensation::OVERTIME)
                ->where('interpolation', false)
                ->first()->pivot->id,
        ]);

        $devSubjectCompany->compensations()->create([
            'name' => '13th Month',
            'order' => 4,
            'assignable' => true,
            'type' => Compensation::BENEFIT,
            'company_formula_id' => $devSubjectCompanyFormulas
                ->where('formulable_type', Formulable::EARNINGS)
                ->where('component_type', Compensation::BENEFIT)
                ->where('interpolation', false)
                ->first()->pivot->id,
        ]);

        //Create Deduction
        $devSubjectCompany->deductions()->create([
            'name' => 'Tardiness',
            'order' => 1,
            'assignable' => true,
            'type' => Deduction::DEDUCTION,
            'company_formula_id' => $devSubjectCompanyFormulas
                ->where('formulable_type', Formulable::DEDUCTIONS)
                ->where('component_type', Deduction::DEDUCTION)
                ->where('name', 'Standard-Tardiness')
                ->first()->pivot->id,
        ]);

        $devSubjectCompany->deductions()->create([
            'name' => 'Absent',
            'order' => 2,
            'assignable' => true,
            'type' => Deduction::DEDUCTION,
            'company_formula_id' => $devSubjectCompanyFormulas
                ->where('formulable_type', Formulable::DEDUCTIONS)
                ->where('component_type', Deduction::DEDUCTION)
                ->where('name', 'Standard-Absent')
                ->first()->pivot->id,
        ]);

        $devSubjectCompany->deductions()->create([
            'name' => 'SSS-Employed',
            'order' => 3,
            'assignable' => true,
            'type' => Deduction::CONTRIBUTION,
            'company_formula_id' => $devSubjectCompanyFormulas
                ->where('formulable_type', Formulable::DEDUCTIONS)
                ->where('component_type', Deduction::CONTRIBUTION)
                ->where('name', 'Standard-SSS-Employed-Contribution')
                ->first()->pivot->id,
        ]);

        //Create Income Tax
        $devSubjectCompany->incomeTaxes()->create([
            'name' => 'Income Tax',
            'order' => 1,
            'assignable' => true,
            'type' => IncomeTax::WITHHOLDING_TAX,
            'company_formula_id' => $devSubjectCompanyFormulas
                ->where('formulable_type', Formulable::INCOME_TAX)
                ->where('component_type', IncomeTax::WITHHOLDING_TAX)
                ->where('name', 'Standard-Withholding-Tax')
                ->where('interpolation', false)
                ->first()->pivot->id,
        ]);

        $endOfMonthCutOffPeriodPreset = collect($timePeriodPresets)
            ->where('type', TimePeriodType::PAY_PERIOD)
            ->where('name', 'end_of_month_cut_off')
            ->first();

        //Create Pay Period Setting
        $devSubjectCompany->payPeriodSetting()->create([
            'days_to_pay_after_cut_off' => 5,
            'time_period_preset_reference' => $endOfMonthCutOffPeriodPreset['name'],
            'monthly_pay_period' => $endOfMonthCutOffPeriodPreset['monthly_period'],
            'semimonthly_pay_period' => $endOfMonthCutOffPeriodPreset['semimonthly_period'],
        ]);

        Prototype::factory()->count(500)->create();
    }
}
