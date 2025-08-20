<?php

namespace Database\Seeders;

use App\Blueprint\Repositories\PayFrequencyRepository;
use App\Concrete\JsonPresets;
use App\Enums\AccountSubscriptionModules;
use App\Enums\CompanyUserAssignmentType;
use App\Enums\Compensation;
use App\Enums\Deduction;
use App\Enums\Formulable;
use App\Enums\Gender;
use App\Enums\IncomeTax;
use App\Enums\MaritalStatus;
use App\Enums\PayPeriod;
use App\Enums\PayType;
use App\Enums\ShiftType;
use App\Enums\TimePeriodType;
use App\Models\Account;
use App\Models\Formula;
use App\Models\Prototype;
use App\Models\Shift;
use App\Models\TimePeriodPreset;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Nnjeim\World\Models\Country;

class Development extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call(WorldSeeder::class);

        Prototype::factory()->count(500)->create();

        //TimePeriod Presets
        $timePeriodPresets = [
            [
                'type' => TimePeriodType::THIRTEENTH_MONTH,
                'name' => 'november_1st',
                'readable_name' => 'November 1st',
                'yearly_period' => JsonPresets::presetValue('timePeriodPresets', 'yearly_period_november_1st'),
            ],
            [
                'type' => TimePeriodType::PAY_FREQUENCY,
                'name' => 'end_of_month_cut_off',
                'readable_name' => 'End of month',
                'monthly_period' => JsonPresets::presetValue('timePeriodPresets', 'monthly_period_end_of_month_cut_off'),
                'semimonthly_period' => JsonPresets::presetValue('timePeriodPresets', 'semimonthly_period_end_of_month_cut_off'),
            ],
            [
                'type' => TimePeriodType::PAY_FREQUENCY,
                'name' => '10th_cut_off',
                'readable_name' => '10th',
                'monthly_period' => JsonPresets::presetValue('timePeriodPresets', 'monthly_period_10th_cut_off'),
                'semimonthly_period' => JsonPresets::presetValue('timePeriodPresets', 'semimonthly_period_10th_cut_off'),
            ],
            [
                'type' => TimePeriodType::PAY_FREQUENCY,
                'name' => '25th_cut_off',
                'readable_name' => '25th',
                'monthly_period' => JsonPresets::presetValue('timePeriodPresets', 'monthly_period_25th_cut_off'),
                'semimonthly_period' => JsonPresets::presetValue('timePeriodPresets', 'semimonthly_period_25th_cut_off'),
            ],
            [
                'type' => TimePeriodType::NIGHT_DIFFERENTIAL_HOURS,
                'name' => 'night_differential_hours',
                'readable_name' => 'Night Differential Hours',
                'hour_period' => JsonPresets::presetValue('timePeriodPresets', 'night_differential_hours'),
            ]
        ];

        foreach ($timePeriodPresets as $timePeriodPreset) {
            TimePeriodPreset::create($timePeriodPreset);
        }

        //Formula Presets
        $formulaPresets = [
            //Earnings
            ['name' => 'Standard-Salary', 'formulable_type' => Formulable::EARNINGS ,'component_type' => Compensation::BASIC_SALARY, 'interpolation' => false,
                'default_settings' => JsonPresets::presetValue('formulableSettingPresets', 'standard_basic_salary')
            ],
            ['name' => 'Standard-Overtime', 'formulable_type' => Formulable::EARNINGS ,'component_type' => Compensation::OVERTIME, 'interpolation' => false,
                'default_settings' => JsonPresets::presetValue('formulableSettingPresets', 'standard_overtime')
            ],
            ['name' => 'Standard-Meal', 'formulable_type' => Formulable::EARNINGS ,'component_type' => Compensation::REGULAR_ALLOWANCE, 'interpolation' => false,
                'default_settings' => JsonPresets::presetValue('formulableSettingPresets', 'standard_meal')
            ],
            ['name' => 'Standard-13th-Month', 'formulable_type' => Formulable::EARNINGS ,'component_type' => Compensation::BENEFIT, 'interpolation' => false,
                'default_settings' => JsonPresets::presetValue('formulableSettingPresets', 'standard_13th_month')
            ],

            //Deductions
            ['name' => 'Standard-Tardiness', 'formulable_type' => Formulable::DEDUCTIONS  ,'component_type' => Deduction::DEDUCTION, 'interpolation' => false,
                'default_settings' => JsonPresets::presetValue('formulableSettingPresets', 'standard_tardiness')
            ],
            ['name' => 'Standard-Absence', 'formulable_type' => Formulable::DEDUCTIONS  ,'component_type' => Deduction::DEDUCTION, 'interpolation' => false,
                'default_settings' => JsonPresets::presetValue('formulableSettingPresets', 'standard_absence')
            ],
            ['name' => 'Standard-SSS-Employed-Contribution', 'formulable_type' => Formulable::DEDUCTIONS  ,'component_type' => Deduction::CONTRIBUTION, 'interpolation' => false,
                'default_settings' => JsonPresets::presetValue('formulableSettingPresets', 'standard_sss_employed_contribution')
            ],
            ['name' => 'Standard-Philhealth-Contribution', 'formulable_type' => Formulable::DEDUCTIONS  ,'component_type' => Deduction::CONTRIBUTION, 'interpolation' => false,
                'default_settings' => JsonPresets::presetValue('formulableSettingPresets', 'standard_philhealth_contribution')
            ],
            ['name' => 'Standard-Pagibig-Contribution', 'formulable_type' => Formulable::DEDUCTIONS ,'component_type' => Deduction::CONTRIBUTION, 'interpolation' => false,
                'default_settings' => JsonPresets::presetValue('formulableSettingPresets', 'standard_pagibig_contribution')
            ],

            //Taxable Income
            ['name' => 'Standard-Taxable-Income', 'formulable_type' => Formulable::TAXABLE_INCOME ,'component_type' => null, 'interpolation' => true],

            //Non-taxable Income
            ['name' => 'Standard-Nontaxable-Income', 'formulable_type' => Formulable::NONTAXABLE_INCOME ,'component_type' => null, 'interpolation' => true],

            //Income Tax
            ['name' => 'Standard-Compensation-Tax', 'formulable_type' => Formulable::INCOME_TAX ,'component_type' => IncomeTax::COMPENSATION_TAX, 'interpolation' => false,
                'default_settings' => JsonPresets::presetValue('formulableSettingPresets', 'standard_compensation_tax')
            ],

            //Net Income
            ['name' => 'Standard-Net-Income', 'formulable_type' => Formulable::NET_INCOME ,'component_type' => null, 'interpolation' => true]
        ];

        foreach ($formulaPresets as $formula) {
            Formula::create($formula);
        }

        /**************************************************************************************************************************************************************************************************************/

        //Superadmin
        $superAdmin = User::factory()->superAdmin()->create(['name' => 'kim.123', 'email' => 'luxere20@gmail.com', 'timezone' => 'Asia/Manila', 'ulid' => Str::ulid(),]);

        //Account 1001
        $account1001 = Account::factory()->standard()->create(['number' => 'ACCOUNT20251001', 'ulid' => Str::ulid(), 'date_registered' => Carbon::now()->toDateTimeString()]);
        //Account 1002
        $account1002 = Account::factory()->standard()->create(['number' => 'ACCOUNT20251002', 'ulid' => Str::ulid(), 'date_registered' => Carbon::now()->toDateTimeString(),]);
        //Account 1003
        $account1003 = Account::factory()->standard()->create(['number' => 'ACCOUNT20251003', 'ulid' => Str::ulid(), 'date_registered' => Carbon::now()->toDateTimeString(),]);

        $account1001->subscriptions()->create(['module' => AccountSubscriptionModules::PAYROLL, 'date_subscribed' => Carbon::now()->toDateTimeString()]);
        $account1002->subscriptions()->create(['module' => AccountSubscriptionModules::PAYROLL, 'date_subscribed' => Carbon::now()->toDateTimeString()]);
        $account1003->subscriptions()->create(['module' => AccountSubscriptionModules::PAYROLL, 'date_subscribed' => Carbon::now()->toDateTimeString()]);

        $philippines = Country::where('iso2', 'PH')->first();

        //Account 1001 Companies
        $company1001A = $account1001->companies()->create(['name' => 'Company 1001-A', 'code' => '1001-A', 'country_id' => $philippines->id, 'currency' => 'PHP', 'timezone' => 'Asia/Manila', 'ulid' => Str::ulid(),]);

        //Account 1002 Companies
        $company1002A = $account1002->companies()->create(['name' => 'Company 1002-A', 'code' => '1002-A', 'country_id' => $philippines->id, 'currency' => 'PHP', 'timezone' => 'Asia/Manila', 'ulid' => Str::ulid(),]);
        $company1002B = $account1002->companies()->create(['name' => 'Company 1002-B', 'code' => '1002-B', 'country_id' => $philippines->id, 'currency' => 'PHP', 'timezone' => 'Asia/Manila', 'ulid' => Str::ulid(),]);
        $company1002C = $account1002->companies()->create(['name' => 'Company 1002-C', 'code' => '1002-C', 'country_id' => $philippines->id, 'currency' => 'PHP', 'timezone' => 'Asia/Manila', 'ulid' => Str::ulid(),]);
        $company1002D = $account1002->companies()->create(['name' => 'Company 1002-D', 'code' => '1002-D', 'country_id' => $philippines->id, 'currency' => 'PHP', 'timezone' => 'Asia/Manila', 'ulid' => Str::ulid(),]);

        //Account 1001 Companies
        $company1003A = $account1003->companies()->create(['name' => 'Company 1003-A', 'code' => '1003-A', 'country_id' => $philippines->id, 'currency' => 'PHP', 'timezone' => 'Asia/Manila', 'ulid' => Str::ulid(),]);

        //Account 1002User01
        $account1002User01 = User::factory()->default()->create(['name' => '1002.user.1', 'email' => 'luxere20@gmail.com', 'ulid' => Str::ulid(),]);
        $account1002User02 = User::factory()->default()->create(['name' => '1002.user.2', 'email' => 'luxere20@gmail.com', 'ulid' => Str::ulid(), 'created_by' => $account1002User01->id,]);
        $account1002User03 = User::factory()->default()->create(['name' => '1002.user.3', 'email' => 'luxere20@gmail.com', 'ulid' => Str::ulid(), 'created_by' => $account1002User01->id,]);
        $account1002User04 = User::factory()->default()->create(['name' => '1002.user.4', 'email' => 'luxere20@gmail.com', 'ulid' => Str::ulid(), 'created_by' => $account1002User01->id,]);
        $user05 = User::factory()->default()->create(['name' => 'user.5', 'email' => 'luxere20@gmail.com', 'ulid' => Str::ulid(), 'created_by' => $account1002User01->id,]);

        //Company 1002-C Shifts
        $company1002C->shifts()->create(['ulid' => Str::ulid(), 'code' => 'DSR2DONF1', 'name' => 'REGULAR 2 DAYS OFF[SUN,SAT] 09:00 AM to 05:00 PM', 'type' => ShiftType::REGULAR]);
        $company1002C->shifts()->create(['ulid' => Str::ulid(), 'code' => 'DSR1DONF1', 'name' => 'REGULAR 1 DAY OFF[SUN] 09:00 AM to 05:00 PM', 'type' => ShiftType::REGULAR]);
        $company1002C->shifts()->create(['ulid' => Str::ulid(), 'code' => 'DSRNDONF1', 'name' => 'REGULAR NO DAY OFF 09:00 AM to 05:00 PM', 'type' => ShiftType::REGULAR]);

        $this->createShiftSchedules(Shift::where('code', 'DSR2DONF1')->first(), false, [CarbonInterface::SUNDAY, CarbonInterface::SATURDAY]);
        $this->createShiftSchedules(Shift::where('code', 'DSR1DONF1')->first(), false, [CarbonInterface::SUNDAY]);
        $this->createShiftSchedules(Shift::where('code', 'DSRNDONF1')->first());
        /*
         * Employee: has employee info and default assigned to a company
         * Employee Admin: has employee info and admin assigned to a company
         * Admin: no employee info and admin assigned to a company
         * */

        //Assign 1002User01 to Company 1001-A as Admin
        $account1002User01->companies()->syncWithoutDetaching([$company1001A->id => ['assignment_type' => CompanyUserAssignmentType::ADMIN]]);

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

        //Assign 1002User03 to Company 1002-C as Employee
        $account1002User03->companies()->syncWithoutDetaching([$company1002C->id => ['assignment_type' => CompanyUserAssignmentType::DEFAULT]]);

        //Assign 1002User04 to Company 1002-C as Admin
        $account1002User04->companies()->syncWithoutDetaching([$company1002C->id => ['assignment_type' => CompanyUserAssignmentType::ADMIN]]);

        //Assign 1002User04 to Company 1002-D as Admin
        $account1002User04->companies()->syncWithoutDetaching([$company1002D->id => ['assignment_type' => CompanyUserAssignmentType::ADMIN]]);
        /**************************************************************************************************************************************************************************************************************/

        //Company 1002-B, 1002-C Salary Statement Modules
        $salaryStatementModules = [
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

        foreach ($salaryStatementModules as $salaryStatementModule) {
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

        // Company 1001-A, 1002-A, 1002-B, 1002-C, and 1002-D Pay Frequencies
        foreach (App::make(PayFrequencyRepository::class)->defaultPresets() as $payFrequency) {
            $company1001A->payFrequencies()->create(['ulid' => Str::ulid(), ...$payFrequency]);
            $company1002A->payFrequencies()->create(['ulid' => Str::ulid(), ...$payFrequency]);
            $company1002B->payFrequencies()->create(['ulid' => Str::ulid(), ...$payFrequency]);
            $company1002C->payFrequencies()->create(['ulid' => Str::ulid(), ...$payFrequency]);
            $company1002D->payFrequencies()->create(['ulid' => Str::ulid(), ...$payFrequency]);
        }

        //Company 1002-B, 1002-C Pre-create Compensations
        $compensationsPresets = [
            ['code' => 'BS', 'name' => 'Basic Salary', 'assignable' => true, 'type' => Compensation::BASIC_SALARY, 'formula' => 'Standard-Salary'],
            ['code' => 'MA', 'name' => 'Meal Allowance', 'assignable' => true, 'type' => Compensation::REGULAR_ALLOWANCE, 'formula' => 'Standard-Meal'],
            ['code' => 'OT', 'name' => 'Overtime', 'assignable' => true, 'type' => Compensation::OVERTIME, 'formula' => 'Standard-Overtime'],
            ['code' => '13THMONTH', 'name' => '13th Month', 'assignable' => true, 'type' => Compensation::BENEFIT, 'formula' => 'Standard-13th-Month'],
        ];

        foreach ($compensationsPresets as $index => $compensationPreset) {
            $this->createPayrollComponent($company1002B, $index, Formulable::EARNINGS, 'compensations', $compensationPreset);
            $this->createPayrollComponent($company1002C, $index, Formulable::EARNINGS, 'compensations', $compensationPreset);
        }

        //Company 1002-B, 1002-C Pre-create Deductions
        $deductionsPresets = [
            ['code' => 'TRD', 'name' => 'Tardiness', 'assignable' => true, 'type' => Deduction::DEDUCTION, 'formula' => 'Standard-Tardiness'],
            ['code' => 'ABS', 'name' => 'Absent', 'assignable' => true, 'type' => Deduction::DEDUCTION, 'formula' => 'Standard-Absence'],
            ['code' => 'SSE', 'name' => 'SSS-Employed', 'assignable' => true, 'type' => Deduction::CONTRIBUTION, 'formula' => 'Standard-SSS-Employed-Contribution'],
        ];

        foreach ($deductionsPresets as $index => $deductionsPreset) {
            $this->createPayrollComponent($company1002B, $index, Formulable::DEDUCTIONS, 'deductions', $deductionsPreset);
            $this->createPayrollComponent($company1002C, $index, Formulable::DEDUCTIONS, 'deductions', $deductionsPreset);
        }

        //Company 1002-B, 1002-C Pre-create Income Taxes
        $incomeTaxesPresets = [
            ['code' => 'CTX', 'name' => 'Compensation Tax', 'assignable' => true, 'type' => IncomeTax::COMPENSATION_TAX, 'formula' => 'Standard-Compensation-Tax'],
        ];

        foreach ($incomeTaxesPresets as $index => $incomeTaxesPreset) {
            $this->createPayrollComponent($company1002B, $index, Formulable::INCOME_TAX, 'incomeTaxes', $incomeTaxesPreset);
            $this->createPayrollComponent($company1002C, $index, Formulable::INCOME_TAX, 'incomeTaxes', $incomeTaxesPreset);
        }

        /**************************************************************************************************************************************************************************************************************/

        //Create Departments to Company 1002-B
        $company1002B->departments()->create(['name' => 'Executive']);
        $company1002B->departments()->create(['name' => 'HR']);
        $company1002BHrDepartment = $company1002B->departments()->where('name', 'HR')->first();
        $company1002B->departments()->create(['name' => 'Payroll', 'parent_id' => $company1002BHrDepartment->id]);
        $company1002B->departments()->create(['name' => 'Training & Development', 'parent_id' => $company1002BHrDepartment->id]);
        $company1002B->departments()->create(['name' => 'Finance & Accounting']);
        $company1002BFinanceAndAccountingDepartment = $company1002B->departments()->where('name', 'Finance & Accounting')->first();
        $company1002B->departments()->create(['name' => 'Accounts Payable', 'parent_id' => $company1002BFinanceAndAccountingDepartment->id]);
        $company1002B->departments()->create(['name' => 'Internal Audit', 'parent_id' => $company1002BFinanceAndAccountingDepartment->id]);

        //Create Departments to Company 1002-C
        $company1002C->departments()->create(['name' => 'Executive']);
        $company1002C->departments()->create(['name' => 'HR']);
        $company1002CHrDepartment = $company1002C->departments()->where('name', 'HR')->first();
        $company1002C->departments()->create(['name' => 'Payroll', 'parent_id' => $company1002CHrDepartment->id]);
        $company1002C->departments()->create(['name' => 'Training & Development', 'parent_id' => $company1002CHrDepartment->id]);
        $company1002C->departments()->create(['name' => 'Finance & Accounting']);
        $company1002CFinanceAndAccountingDepartment = $company1002C->departments()->where('name', 'Finance & Accounting')->first();
        $company1002C->departments()->create(['name' => 'Accounts Payable', 'parent_id' => $company1002CFinanceAndAccountingDepartment->id]);
        $company1002C->departments()->create(['name' => 'Internal Audit', 'parent_id' => $company1002CFinanceAndAccountingDepartment->id]);

        /**************************************************************************************************************************************************************************************************************/

        //Create Designations to Company 1002-B
        $company1002B->designations()->create(['name' => 'CEO']);
        $company1002B->designations()->create(['name' => 'HR Manager']);
        $company1002B->designations()->create(['name' => 'HR Assistant']);
        $company1002B->designations()->create(['name' => 'Account Manager']);
        $company1002B->designations()->create(['name' => 'Accounting Staff']);

        //Create Designations to Company 1002-C
        $company1002C->designations()->create(['name' => 'CEO']);
        $company1002C->designations()->create(['name' => 'HR Manager']);
        $company1002C->designations()->create(['name' => 'HR Assistant']);
        $company1002C->designations()->create(['name' => 'Account Manager']);
        $company1002C->designations()->create(['name' => 'Accounting Staff']);

        /**************************************************************************************************************************************************************************************************************/

        //Create Employee Info A1001 to Company 1002-A
        $employeeA1001 = $account1002User01->employees()->create([
            'ulid' => Str::ulid(),
            'company_id' => $company1002A->id,
            'number' => 'A1001',
            'given_name' => 'Employee 01',
            'middle_name' => 'A',
            'family_name' => '1002',
            'gender' => Gender::FEMALE,
            'marital_status' => MaritalStatus::SINGLE,
        ]);

        //Create Contact info for Employee A1001
        $employeeA1001->contact()->create(['office_email' => 'a1001.01@officemail.com', 'personal_email' => 'a1001.01@personalmail.com', 'office_phone' => '+639123456789', 'personal_phone' => '+639123456789']);

        //Create Employee Info B1001 to Company 1002-B
        $employeeB1001 = $account1002User02->employees()->create([
            'ulid' => Str::ulid(),
            'company_id' => $company1002B->id,
            'department_id' => $company1002B->departments()->where('name', 'Accounts Payable')->first()->id,
            'designation_id' => $company1002B->designations()->where('name', 'Accounting Staff')->first()->id,
            'number' => 'B1001',
            'given_name' => 'Employee 01',
            'middle_name' => 'B',
            'family_name' => '1002',
            'gender' => Gender::FEMALE,
            'marital_status' => MaritalStatus::SINGLE,
        ]);

        //Create Contact info for Employee B1001
        $employeeB1001->contact()->create(['office_email' => 'b1001.01@officemail.com']);

        //Create Employee Info C1001 to Company 1002-C
        $employeeC1001 = $account1002User01->employees()->create([
            'ulid' => Str::ulid(),
            'company_id' => $company1002C->id,
            'department_id' => $company1002C->departments()->where('name', 'HR')->first()->id,
            'designation_id' => $company1002C->designations()->where('name', 'HR Manager')->first()->id,
            'number' => 'C1001',
            'given_name' => 'Employee 01',
            'middle_name' => 'C',
            'family_name' => '1002',
            'gender' => Gender::FEMALE,
            'marital_status' => MaritalStatus::SINGLE,
        ]);

        //Create Contact info for Employee C1001
        $employeeC1001->contact()->create(['office_email' => 'c1001.01@officemail.com', 'personal_email' => 'c1001.01@personalmail.com']);

        //Create Employee Info C1002 to Company 1002-C
        $employeeC1002 = $account1002User02->employees()->create([
            'ulid' => Str::ulid(),
            'company_id' => $company1002C->id,
            'department_id' => $company1002C->departments()->where('name', 'Accounts Payable')->first()->id,
            'designation_id' => $company1002C->designations()->where('name', 'Accounting Staff')->first()->id,
            'manager_id' => $employeeC1001->id,
            'number' => 'C1002',
            'given_name' => 'Employee 02',
            'middle_name' => 'C',
            'family_name' => '1002',
            'gender' => Gender::FEMALE,
            'marital_status' => MaritalStatus::SINGLE,
        ]);

        //Create Contact info for Employee C1002
        $employeeC1002->contact()->create(['office_email' => 'c1002.01@officemail.com', 'personal_email' => 'c1002.01@personalmail.com', 'office_phone' => '+639122256789']);

        //Create Employee Info C1003 to Company 1002-C
        $employeeC1003 = $account1002User03->employees()->create([
            'ulid' => Str::ulid(),
            'company_id' => $company1002C->id,
            'department_id' => $company1002C->departments()->where('name', 'Accounts Payable')->first()->id,
            'designation_id' => $company1002C->designations()->where('name', 'Accounting Staff')->first()->id,
            'manager_id' => $employeeC1001->id,
            'number' => 'C1003',
            'given_name' => 'Employee 03',
            'middle_name' => 'C',
            'family_name' => '1002',
            'gender' => Gender::NOT_SPECIFIED,
            'marital_status' => MaritalStatus::SINGLE,
        ]);

        //Create Contact info for Employee C1003
        $employeeC1003->contact()->create(['office_email' => 'c1003.01@officemail.com', 'personal_phone' => '+639122111789']);

        /**************************************************************************************************************************************************************************************************************/

        //Company 1002-B Monthly Pay Frequency
        $company1002BMonthlyPayFrequency = $company1002B->payFrequencies()->where('code', 'MONTHLY')->first();

        //Company 1002-B Compensations
        $company1002BBasicSalary = $company1002B->compensations->where('name', 'Basic Salary')->where('type', Compensation::BASIC_SALARY)->first();
        $company1002BMealAllowance = $company1002B->compensations->where('name', 'Meal Allowance')->where('type', Compensation::REGULAR_ALLOWANCE)->first();
        $company1002BOvertime = $company1002B->compensations->where('name', 'Overtime')->where('type', Compensation::OVERTIME)->first();

        //Company 1002-B Deductions
        $company1002BTardiness = $company1002B->deductions->where('name', 'Tardiness')->where('type', Deduction::DEDUCTION)->first();
        $company1002BAbsent = $company1002B->deductions->where('name', 'Absent')->where('type', Deduction::DEDUCTION)->first();
        $company1002BSSSEmployed = $company1002B->deductions->where('name', 'SSS-Employed')->where('type', Deduction::CONTRIBUTION)->first();

        //Company 1002-B Income Taxes
        $company1002BCompensationTax = $company1002B->incomeTaxes->where('name', 'Compensation Tax')->where('type', IncomeTax::COMPENSATION_TAX)->first();

        //Create Compensations for Employee B1001
        $employeeB1001->payrollComponents()->create(['formulable_type' => Formulable::EARNINGS , 'payroll_componentable_id' => $company1002BBasicSalary->id, 'payroll_componentable_type' => 'compensation', 'amount' => '1200.14','currency' => 'PHP', 'pay_period' => PayPeriod::MONTHLY, 'pay_type' => PayType::BY_ATTENDANCE, 'pay_frequency_id' => $company1002BMonthlyPayFrequency->id]);
        $employeeB1001->payrollComponents()->create(['formulable_type' => Formulable::EARNINGS , 'payroll_componentable_id' => $company1002BMealAllowance->id, 'payroll_componentable_type' => 'compensation', 'amount' => '200', 'currency' => 'PHP', 'pay_period' => PayPeriod::MONTHLY, 'pay_type' => PayType::BY_ATTENDANCE, 'pay_frequency_id' => $company1002BMonthlyPayFrequency->id]);
        $employeeB1001->payrollComponents()->create(['formulable_type' => Formulable::EARNINGS , 'payroll_componentable_id' => $company1002BOvertime->id, 'payroll_componentable_type' => 'compensation']);
        //Create Deductions for Employee B1001
        $employeeB1001->payrollComponents()->create(['formulable_type' => Formulable::DEDUCTIONS , 'payroll_componentable_id' => $company1002BTardiness->id, 'payroll_componentable_type' => 'deduction']);
        $employeeB1001->payrollComponents()->create(['formulable_type' => Formulable::DEDUCTIONS , 'payroll_componentable_id' => $company1002BAbsent->id, 'payroll_componentable_type' => 'deduction']);
        $employeeB1001->payrollComponents()->create(['formulable_type' => Formulable::DEDUCTIONS , 'payroll_componentable_id' => $company1002BSSSEmployed->id, 'payroll_componentable_type' => 'deduction']);
        //Create Income Tax for Employee B1001
        $employeeB1001->payrollComponents()->create(['formulable_type' => Formulable::INCOME_TAX , 'payroll_componentable_id' => $company1002BCompensationTax->id, 'payroll_componentable_type' => 'income_tax']);

        //Company 1002-C Monthly Pay Frequency
        $company1002CMonthlyPayFrequency = $company1002C->payFrequencies()->where('code', 'MONTHLY')->first();

        //Company 1002-C Compensations
        $company1002CBasicSalary = $company1002C->compensations->where('name', 'Basic Salary')->where('type', Compensation::BASIC_SALARY)->first();
        $company1002CMealAllowance = $company1002C->compensations->where('name', 'Meal Allowance')->where('type', Compensation::REGULAR_ALLOWANCE)->first();
        $company1002COvertime = $company1002C->compensations->where('name', 'Overtime')->where('type', Compensation::OVERTIME)->first();

        //Company 1002-C Deductions
        $company1002CTardiness = $company1002C->deductions->where('name', 'Tardiness')->where('type', Deduction::DEDUCTION)->first();
        $company1002CAbsent = $company1002C->deductions->where('name', 'Absent')->where('type', Deduction::DEDUCTION)->first();
        $company1002CSSSEmployed = $company1002C->deductions->where('name', 'SSS-Employed')->where('type', Deduction::CONTRIBUTION)->first();

        //Company 1002-C Income Taxes
        $company1002CCompensationTax = $company1002C->incomeTaxes->where('name', 'Compensation Tax')->where('type', IncomeTax::COMPENSATION_TAX)->first();

        //Create Compensations for Employee C1001
        $employeeC1001->payrollComponents()->create(['formulable_type' => Formulable::EARNINGS , 'payroll_componentable_id' => $company1002CBasicSalary->id, 'payroll_componentable_type' => 'compensation', 'amount' => '1200.14', 'currency' => 'PHP', 'pay_period' => PayPeriod::MONTHLY, 'pay_type' => PayType::BY_ATTENDANCE, 'pay_frequency_id' => $company1002CMonthlyPayFrequency->id]);
        $employeeC1001->payrollComponents()->create(['formulable_type' => Formulable::EARNINGS , 'payroll_componentable_id' => $company1002CMealAllowance->id, 'payroll_componentable_type' => 'compensation', 'amount' => '200', 'currency' => 'PHP', 'pay_period' => PayPeriod::MONTHLY, 'pay_type' => PayType::BY_ATTENDANCE, 'pay_frequency_id' => $company1002CMonthlyPayFrequency->id]);
        $employeeC1001->payrollComponents()->create(['formulable_type' => Formulable::EARNINGS , 'payroll_componentable_id' => $company1002COvertime->id, 'payroll_componentable_type' => 'compensation']);
        //Create Deductions for Employee C1001
        $employeeC1001->payrollComponents()->create(['formulable_type' => Formulable::DEDUCTIONS , 'payroll_componentable_id' => $company1002CTardiness->id, 'payroll_componentable_type' => 'deduction']);
        $employeeC1001->payrollComponents()->create(['formulable_type' => Formulable::DEDUCTIONS , 'payroll_componentable_id' => $company1002CAbsent->id, 'payroll_componentable_type' => 'deduction']);
        $employeeC1001->payrollComponents()->create(['formulable_type' => Formulable::DEDUCTIONS , 'payroll_componentable_id' => $company1002CSSSEmployed->id, 'payroll_componentable_type' => 'deduction']);
        //Create Income Tax for Employee C1001
        $employeeC1001->payrollComponents()->create(['formulable_type' => Formulable::INCOME_TAX , 'payroll_componentable_id' => $company1002CCompensationTax->id, 'payroll_componentable_type' => 'income_tax']);

        //Create Compensations for Employee C1002
        $employeeC1002->payrollComponents()->create(['formulable_type' => Formulable::EARNINGS , 'payroll_componentable_id' => $company1002CBasicSalary->id, 'payroll_componentable_type' => 'compensation', 'amount' => '100', 'currency' => 'PHP', 'pay_period' => PayPeriod::MONTHLY, 'pay_type' => PayType::BY_ATTENDANCE, 'pay_frequency_id' => $company1002CMonthlyPayFrequency->id]);
        $employeeC1002->payrollComponents()->create(['formulable_type' => Formulable::EARNINGS , 'payroll_componentable_id' => $company1002CMealAllowance->id, 'payroll_componentable_type' => 'compensation', 'amount' => '10', 'currency' => 'PHP', 'pay_period' => PayPeriod::MONTHLY, 'pay_type' => PayType::BY_ATTENDANCE, 'pay_frequency_id' => $company1002CMonthlyPayFrequency->id]);
        $employeeC1002->payrollComponents()->create(['formulable_type' => Formulable::EARNINGS , 'payroll_componentable_id' => $company1002COvertime->id, 'payroll_componentable_type' => 'compensation']);
        //Create Deductions for Employee C1002
        $employeeC1002->payrollComponents()->create(['formulable_type' => Formulable::DEDUCTIONS , 'payroll_componentable_id' => $company1002CTardiness->id, 'payroll_componentable_type' => 'deduction']);
        $employeeC1002->payrollComponents()->create(['formulable_type' => Formulable::DEDUCTIONS , 'payroll_componentable_id' => $company1002CAbsent->id, 'payroll_componentable_type' => 'deduction']);
        $employeeC1002->payrollComponents()->create(['formulable_type' => Formulable::DEDUCTIONS , 'payroll_componentable_id' => $company1002CSSSEmployed->id, 'payroll_componentable_type' => 'deduction']);
        //Create Income Tax for Employee C1002
        $employeeC1002->payrollComponents()->create(['formulable_type' => Formulable::INCOME_TAX , 'payroll_componentable_id' => $company1002CCompensationTax->id, 'payroll_componentable_type' => 'income_tax']);

        //Create Compensations for Employee C1003
        $employeeC1003->payrollComponents()->create(['formulable_type' => Formulable::EARNINGS , 'payroll_componentable_id' => $company1002CBasicSalary->id, 'payroll_componentable_type' => 'compensation', 'amount' => '420', 'currency' => 'PHP', 'pay_period' => PayPeriod::MONTHLY, 'pay_type' => PayType::BY_ATTENDANCE, 'pay_frequency_id' => $company1002CMonthlyPayFrequency->id]);
        $employeeC1003->payrollComponents()->create(['formulable_type' => Formulable::EARNINGS , 'payroll_componentable_id' => $company1002CMealAllowance->id, 'payroll_componentable_type' => 'compensation', 'amount' => '20', 'currency' => 'PHP', 'pay_period' => PayPeriod::MONTHLY, 'pay_type' => PayType::BY_ATTENDANCE, 'pay_frequency_id' => $company1002CMonthlyPayFrequency->id]);
        $employeeC1003->payrollComponents()->create(['formulable_type' => Formulable::EARNINGS , 'payroll_componentable_id' => $company1002COvertime->id, 'payroll_componentable_type' => 'compensation']);
        //Create Deductions for Employee C1003
        $employeeC1003->payrollComponents()->create(['formulable_type' => Formulable::DEDUCTIONS , 'payroll_componentable_id' => $company1002CTardiness->id, 'payroll_componentable_type' => 'deduction']);
        $employeeC1003->payrollComponents()->create(['formulable_type' => Formulable::DEDUCTIONS , 'payroll_componentable_id' => $company1002CAbsent->id, 'payroll_componentable_type' => 'deduction']);
        $employeeC1003->payrollComponents()->create(['formulable_type' => Formulable::DEDUCTIONS , 'payroll_componentable_id' => $company1002CSSSEmployed->id, 'payroll_componentable_type' => 'deduction']);
        //Create Income Tax for Employee C1003
        $employeeC1003->payrollComponents()->create(['formulable_type' => Formulable::INCOME_TAX , 'payroll_componentable_id' => $company1002CCompensationTax->id, 'payroll_componentable_type' => 'income_tax']);
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

    public function createShiftSchedules(Shift $shift, $flexible = false, $dayoffs = []): void
    {
        $restDays = [
            CarbonInterface::SUNDAY,
            CarbonInterface::SATURDAY,
        ];

        $weekdays = [
            CarbonInterface::SUNDAY,
            CarbonInterface::MONDAY,
            CarbonInterface::TUESDAY,
            CarbonInterface::WEDNESDAY,
            CarbonInterface::THURSDAY,
            CarbonInterface::FRIDAY,
            CarbonInterface::SATURDAY,
        ];

        foreach ($weekdays as $weekday) {

            $dayOff = in_array($weekday, $dayoffs);

            if($dayOff){

                $shift->schedules()->create([
                    'week_day' => $weekday,
                    'is_rest_day' => in_array($weekday, $restDays),
                    'is_day_off' => true,
                    'timezone' => null,
                    'is_flexible' => $flexible,
                    'work_start' => null,
                    'work_end' => null,
                    'total_work_hours_with_breaks' => null,
                    'has_lunch_break' => false,
                    'lunch_break_start' => null,
                    'lunch_break_end' => null,
                    'total_lunch_break_hours' => null,
                    'is_lunch_break_compensable' => false
                ]);

            } else {

                $shift->schedules()->create([
                    'week_day' => $weekday,
                    'is_rest_day' => in_array($weekday, $restDays),
                    'is_day_off' => false,
                    'timezone' => 'Asia/Manila',
                    'is_flexible' => $flexible,
                    'work_start' => '09:00',
                    'work_end' => '17:00',
                    'total_work_hours_with_breaks' => '08:00',
                    'has_lunch_break' => true,
                    'lunch_break_start' => '12:00',
                    'lunch_break_end' => '13:00',
                    'total_lunch_break_hours' => '01:00',
                    'is_lunch_break_compensable' => false
                ]);
            }
        }
    }
}
