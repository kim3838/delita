<?php

namespace Database\Seeders;

use App\Enums\CompanyUserAssignmentType;
use App\Enums\Compensation;
use App\Enums\Deduction;
use App\Enums\Formulable;
use App\Enums\IncomeTax;
use App\Models\Account;
use App\Models\Company;
use App\Models\Formula;
use App\Models\Prototype;
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
            ->create(['name' => 'kim.123', 'email' => 'luxere20@gmail.com']);

        $defaultUser = User::factory()->default()
            ->create(['name' => 'user.123', 'email' => 'kimdeguzman20@yahoo.com']);

        User::factory(8)->create();

        $accounts = Account::factory()->count(3)->has(Company::factory()->count(2))->create();

        $defaultUser->companies()->syncWithoutDetaching([1 => ['assignment_type' => CompanyUserAssignmentType::DEFAULT]]);
        $defaultUser->companies()->syncWithoutDetaching([2 => ['assignment_type' => CompanyUserAssignmentType::ADMIN]]);

        $formulas = [
            ['name' => 'Standard-Salary', 'formulable_type' => Formulable::EARNINGS ,'component_type' => Compensation::SALARY, 'interpolation' => false],
            ['name' => 'Standard-Meal', 'formulable_type' => Formulable::EARNINGS ,'component_type' => Compensation::REGULAR_ALLOWANCE, 'interpolation' => false],
            ['name' => 'Standard-Overtime', 'formulable_type' => Formulable::EARNINGS ,'component_type' => Compensation::OVERTIME, 'interpolation' => false],
            ['name' => 'Standard-13th-Month', 'formulable_type' => Formulable::EARNINGS  ,'component_type' => Compensation::BENEFIT, 'interpolation' => false,
                'default_settings' => [
                    [
                        'key' => 'start_date',
                        'label' => 'Start Date',
                        'order' => 1,
                        'type' => 'date',
                        'value_type' => 'natural_language',
                        'value' => 'Nov 02 last year'
                    ],[
                        'key' => 'end_date',
                        'label' => 'End Date',
                        'order' => 2,
                        'type' => 'date',
                        'value_type' => 'natural_language',
                        'value' => 'Nov 01 current year'
                    ]
                ]
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
                    $company->formulas()->syncWithoutDetaching([$formula->id => ['settings' => isset($formula->default_settings) ? json_encode($formula->default_settings) : null] ]);
                }
            }

            foreach ($account->companies as $company) {

                $companyFormulas = $company->formulas;

                $company->compensations()->create([
                    'name' => 'Basic Salary',
                    'order' => 1,
                    'assignable' => true,
                    'type' => Compensation::SALARY,
                    'company_formula_id' => $companyFormulas->where('formulable_type', Formulable::EARNINGS)->where('component_type', Compensation::SALARY)->first()->pivot->id,
                ]);

                $company->compensations()->create([
                    'name' => 'Meal Allowance',
                    'order' => 2,
                    'assignable' => true,
                    'type' => Compensation::REGULAR_ALLOWANCE,
                    'company_formula_id' => $companyFormulas->where('formulable_type', Formulable::EARNINGS)->where('component_type', Compensation::REGULAR_ALLOWANCE)->first()->pivot->id,
                ]);

                $company->compensations()->create([
                    'name' => 'Overtime',
                    'order' => 3,
                    'assignable' => true,
                    'type' => Compensation::OVERTIME,
                    'company_formula_id' => $companyFormulas->where('formulable_type', Formulable::EARNINGS)->where('component_type', Compensation::OVERTIME)->first()->pivot->id,
                ]);

                $company->compensations()->create([
                    'name' => '13th Month',
                    'order' => 4,
                    'assignable' => true,
                    'type' => Compensation::BENEFIT,
                    'company_formula_id' => $companyFormulas->where('formulable_type', Formulable::EARNINGS)->where('component_type', Compensation::BENEFIT)->first()->pivot->id,
                ]);
            }
        }

        Prototype::factory()->count(500)->create();
    }
}
