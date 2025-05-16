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
            ['name' => 'standard-salary', 'formulable_type' => Formulable::EARNINGS ,'component_type' => Compensation::SALARY, 'interpolation' => false],
            ['name' => 'standard-meal', 'formulable_type' => Formulable::EARNINGS ,'component_type' => Compensation::REGULAR_ALLOWANCE, 'interpolation' => false],
            ['name' => 'standard-overtime', 'formulable_type' => Formulable::EARNINGS ,'component_type' => Compensation::OVERTIME, 'interpolation' => false],
            ['name' => 'standard-13th-month', 'formulable_type' => Formulable::EARNINGS  ,'component_type' => Compensation::BENEFIT, 'interpolation' => false],
            ['name' => 'standard-tardiness', 'formulable_type' => Formulable::DEDUCTIONS  ,'component_type' => Deduction::DEDUCTION, 'interpolation' => false],
            ['name' => 'standard-absent', 'formulable_type' => Formulable::DEDUCTIONS  ,'component_type' => Deduction::DEDUCTION, 'interpolation' => false],
            ['name' => 'standard-SSS-employed-contribution', 'formulable_type' => Formulable::DEDUCTIONS  ,'component_type' => Deduction::CONTRIBUTION, 'interpolation' => false],
            ['name' => 'standard-philhealth-contribution', 'formulable_type' => Formulable::DEDUCTIONS  ,'component_type' => Deduction::CONTRIBUTION, 'interpolation' => false],
            ['name' => 'standard-pagibig-contribution', 'formulable_type' => Formulable::DEDUCTIONS ,'component_type' => Deduction::CONTRIBUTION, 'interpolation' => false],
            ['name' => 'standard-taxable-income', 'formulable_type' => Formulable::TAXABLE_INCOME ,'component_type' => null, 'interpolation' => true],
            ['name' => 'standard-nontaxable-income', 'formulable_type' => Formulable::NON_TAXABLE_INCOME ,'component_type' => null, 'interpolation' => true],
            ['name' => 'standard-withholding-tax', 'formulable_type' => Formulable::INCOME_TAX ,'component_type' => IncomeTax::WITHHOLDING_TAX, 'interpolation' => false],
            ['name' => 'standard-net-income', 'formulable_type' => Formulable::NET_INCOME ,'component_type' => null, 'interpolation' => true]
        ];

        foreach ($formulas as $formula) {
            Formula::create($formula)->companies()->sync($account->companies->pluck('id'));
        }

        Prototype::factory()->count(500)->create();
    }
}
