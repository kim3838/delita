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
        $account = Account::factory()->has(Company::factory()->count(4))
            ->create();

        $superAdmin = User::factory()->superAdmin()
            ->create(['name' => 'kim.123', 'email' => 'luxere20@gmail.com']);

        User::factory(9)->create();

        $superAdmin->companies()
            ->syncWithPivotValues(
                $account->companies->pluck('id')->toArray(),
                ['assignment_type' => CompanyUserAssignmentType::ADMIN]
            );

        $formulas = [
            ['name' => 'Standard-Salary', 'formulable_type' => Formulable::EARNINGS ,'component_type' => Compensation::SALARY, 'interpolation' => false],
            ['name' => 'Standard-Meal', 'formulable_type' => Formulable::EARNINGS ,'component_type' => Compensation::REGULAR_ALLOWANCE, 'interpolation' => false],
            ['name' => 'Standard-Overtime', 'formulable_type' => Formulable::EARNINGS ,'component_type' => Compensation::OVERTIME, 'interpolation' => false],
            ['name' => 'Standard-13th-Month', 'formulable_type' => Formulable::EARNINGS  ,'component_type' => Compensation::BENEFIT, 'interpolation' => false, 'default_settings' => ['start_date' => 'Nov 02 of last year', 'end_date' => 'Nov 01 of current year']],
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
            Formula::create($formula)
                ->companies()
                ->syncWithPivotValues(
                    $account->companies->pluck('id'),
                    ['settings' => isset($formula['default_settings']) ? json_encode($formula['default_settings']) : null]
                );
        }

        Prototype::factory()->count(500)->create();
    }
}
