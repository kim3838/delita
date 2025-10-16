<?php

namespace App\Providers;

use App\Blueprint\Repositories\AccountRepository;
use App\Blueprint\Repositories\AssociatedAccountRepository;
use App\Blueprint\Repositories\AssociatedCompanyRepository;
use App\Blueprint\Repositories\AssociatedUserRepository;
use App\Blueprint\Repositories\CompanyCompensationRepository;
use App\Blueprint\Repositories\CompanyDeductionRepository;
use App\Blueprint\Repositories\CompanyFormulaRepository;
use App\Blueprint\Repositories\CompanyIncomeTaxRepository;
use App\Blueprint\Repositories\CompanyRepository;
use App\Blueprint\Repositories\CompensationRepository;
use App\Blueprint\Repositories\DeductionRepository;
use App\Blueprint\Repositories\DepartmentRepository;
use App\Blueprint\Repositories\DesignationRepository;
use App\Blueprint\Repositories\EmployeeContactRepository;
use App\Blueprint\Repositories\EmployeeGroupRepository;
use App\Blueprint\Repositories\EmployeePayrollComponentRepository;
use App\Blueprint\Repositories\EmployeeRepository;
use App\Blueprint\Repositories\EmployeeShiftRepository;
use App\Blueprint\Repositories\EmploymentProfileRepository;
use App\Blueprint\Repositories\FormulaRepository;
use App\Blueprint\Repositories\GroupRepository;
use App\Blueprint\Repositories\IncomeTaxRepository;
use App\Blueprint\Repositories\JsonPresetRepository;
use App\Blueprint\Repositories\NonEmployeeUserRepository;
use App\Blueprint\Repositories\PayFrequencyRepository;
use App\Blueprint\Repositories\PrototypeRepository;
use App\Blueprint\Repositories\SalaryStatementModuleRepository;
use App\Blueprint\Repositories\ShiftRepository;
use App\Blueprint\Repositories\ShiftScheduleRepository;
use App\Blueprint\Repositories\TimePeriodPresetRepository;
use App\Blueprint\Repositories\UserCompanyAssignmentRepository;
use App\Blueprint\Repositories\UserRepository;
use App\Concrete\Repositories\AccountRepositoryEloquent;
use App\Concrete\Repositories\AssociatedAccountRepositoryEloquent;
use App\Concrete\Repositories\AssociatedCompanyRepositoryEloquent;
use App\Concrete\Repositories\AssociatedUserRepositoryEloquent;
use App\Concrete\Repositories\CompanyCompensationRepositoryEloquent;
use App\Concrete\Repositories\CompanyDeductionRepositoryEloquent;
use App\Concrete\Repositories\CompanyFormulaRepositoryEloquent;
use App\Concrete\Repositories\CompanyIncomeTaxRepositoryEloquent;
use App\Concrete\Repositories\CompanyRepositoryEloquent;
use App\Concrete\Repositories\CompensationRepositoryEloquent;
use App\Concrete\Repositories\DeductionRepositoryEloquent;
use App\Concrete\Repositories\DepartmentRepositoryEloquent;
use App\Concrete\Repositories\DesignationRepositoryEloquent;
use App\Concrete\Repositories\EmployeeContactRepositoryEloquent;
use App\Concrete\Repositories\EmployeeGroupRepositoryEloquent;
use App\Concrete\Repositories\EmployeePayrollComponentRepositoryEloquent;
use App\Concrete\Repositories\EmployeeRepositoryEloquent;
use App\Concrete\Repositories\EmployeeShiftRepositoryEloquent;
use App\Concrete\Repositories\EmploymentProfileRepositoryEloquent;
use App\Concrete\Repositories\FormulaRepositoryEloquent;
use App\Concrete\Repositories\GroupRepositoryEloquent;
use App\Concrete\Repositories\IncomeTaxRepositoryEloquent;
use App\Concrete\Repositories\JsonPresetRepositoryEloquent;
use App\Concrete\Repositories\NonEmployeeUserRepositoryEloquent;
use App\Concrete\Repositories\PayFrequencyRepositoryEloquent;
use App\Concrete\Repositories\PrototypeRepositoryEloquent;
use App\Concrete\Repositories\SalaryStatementModuleRepositoryEloquent;
use App\Concrete\Repositories\ShiftRepositoryEloquent;
use App\Concrete\Repositories\ShiftScheduleRepositoryEloquent;
use App\Concrete\Repositories\TimePeriodPresetRepositoryEloquent;
use App\Concrete\Repositories\UserCompanyAssignmentRepositoryEloquent;
use App\Concrete\Repositories\UserRepositoryEloquent;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class RepositoryBindingsServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public $bindings = [
        'account' => AccountRepositoryEloquent::class,
        'associated_account' => AssociatedAccountRepositoryEloquent::class,
        'user' => UserRepositoryEloquent::class,
        'non_employee_user' => NonEmployeeUserRepositoryEloquent::class,
        'user_company_assignment' => UserCompanyAssignmentRepositoryEloquent::class,
        'associated_user' => AssociatedUserRepositoryEloquent::class,
        'prototype' => PrototypeRepositoryEloquent::class,
        'formula' => FormulaRepositoryEloquent::class,
        'json_preset' => JsonPresetRepositoryEloquent::class,
        'company' => CompanyRepositoryEloquent::class,
        'associated_company' => AssociatedCompanyRepositoryEloquent::class,
        'designation' => DesignationRepositoryEloquent::class,
        'department' => DepartmentRepositoryEloquent::class,
        'group' => GroupRepositoryEloquent::class,
        'employee_group' => EmployeeGroupRepositoryEloquent::class,
        'employee' => EmployeeRepositoryEloquent::class,
        'employment_profile' => EmploymentProfileRepositoryEloquent::class,
        'employee_contact' => EmployeeContactRepositoryEloquent::class,
        'employee_payroll_component' => EmployeePayrollComponentRepositoryEloquent::class,
        'employee_shift' => EmployeeShiftRepositoryEloquent::class,
        'company_formula' => CompanyFormulaRepositoryEloquent::class,
        'compensation' => CompensationRepositoryEloquent::class,
        'company_compensation' => CompanyCompensationRepositoryEloquent::class,
        'deduction' => DeductionRepositoryEloquent::class,
        'company_deduction' => CompanyDeductionRepositoryEloquent::class,
        'income_tax' => IncomeTaxRepositoryEloquent::class,
        'company_income_tax' => CompanyIncomeTaxRepositoryEloquent::class,
        'time_period_preset' => TimePeriodPresetRepositoryEloquent::class,
        'salary_statement_module' => SalaryStatementModuleRepositoryEloquent::class,
        'shift' => ShiftRepositoryEloquent::class,
        'shift_schedule' => ShiftScheduleRepositoryEloquent::class,
        'pay_frequency' => PayFrequencyRepositoryEloquent::class,
        AccountRepository::class => AccountRepositoryEloquent::class,
        AssociatedAccountRepository::class => AssociatedAccountRepositoryEloquent::class,
        UserRepository::class => UserRepositoryEloquent::class,
        NonEmployeeUserRepository::class => NonEmployeeUserRepositoryEloquent::class,
        UserCompanyAssignmentRepository::class => UserCompanyAssignmentRepositoryEloquent::class,
        AssociatedUserRepository::class => AssociatedUserRepositoryEloquent::class,
        PrototypeRepository::class => PrototypeRepositoryEloquent::class,
        AssociatedCompanyRepository::class => AssociatedCompanyRepositoryEloquent::class,
        DesignationRepository::class => DesignationRepositoryEloquent::class,
        DepartmentRepository::class => DepartmentRepositoryEloquent::class,
        GroupRepository::class => GroupRepositoryEloquent::class,
        EmployeeGroupRepository::class => EmployeeGroupRepositoryEloquent::class,
        EmployeeRepository::class => EmployeeRepositoryEloquent::class,
        EmploymentProfileRepository::class => EmploymentProfileRepositoryEloquent::class,
        EmployeeContactRepository::class => EmployeeContactRepositoryEloquent::class,
        EmployeePayrollComponentRepository::class => EmployeePayrollComponentRepositoryEloquent::class,
        EmployeeShiftRepository::class => EmployeeShiftRepositoryEloquent::class,
        CompanyFormulaRepository::class => CompanyFormulaRepositoryEloquent::class,
        CompensationRepository::class => CompensationRepositoryEloquent::class,
        CompanyCompensationRepository::class => CompanyCompensationRepositoryEloquent::class,
        DeductionRepository::class => DeductionRepositoryEloquent::class,
        CompanyDeductionRepository::class => CompanyDeductionRepositoryEloquent::class,
        IncomeTaxRepository::class => IncomeTaxRepositoryEloquent::class,
        CompanyIncomeTaxRepository::class => CompanyIncomeTaxRepositoryEloquent::class,
        TimePeriodPresetRepository::class => TimePeriodPresetRepositoryEloquent::class,
        FormulaRepository::class => FormulaRepositoryEloquent::class,
        JsonPresetRepository::class => JsonPresetRepositoryEloquent::class,
        CompanyRepository::class => CompanyRepositoryEloquent::class,
        SalaryStatementModuleRepository::class => SalaryStatementModuleRepositoryEloquent::class,
        ShiftRepository::class => ShiftRepositoryEloquent::class,
        ShiftScheduleRepository::class => ShiftScheduleRepositoryEloquent::class,
        PayFrequencyRepository::class => PayFrequencyRepositoryEloquent::class,
    ];

    public function provides(): array
    {
        return [
            'account',
            'associated_account',
            'user',
            'non_employee_user',
            'user_company_assignment',
            'associated_user',
            'prototype',
            'formula',
            'json_preset',
            'company',
            'associated_company',
            'designation',
            'department',
            'group',
            'employee_group',
            'employee',
            'employment_profile',
            'employee_contact',
            'employee_payroll_component',
            'employee_shift',
            'company_formula',
            'compensation',
            'company_compensation',
            'deduction',
            'company_deduction',
            'income_tax',
            'company_income_tax',
            'time_period_preset',
            'salary_statement_module',
            'shift',
            'shift_schedule',
            'pay_frequency',
            AccountRepository::class,
            AssociatedAccountRepository::class,
            UserRepository::class,
            NonEmployeeUserRepository::class,
            UserCompanyAssignmentRepository::class,
            AssociatedUserRepository::class,
            PrototypeRepository::class,
            FormulaRepository::class,
            JsonPresetRepository::class,
            CompanyRepository::class,
            AssociatedCompanyRepository::class,
            DesignationRepository::class,
            DepartmentRepository::class,
            GroupRepository::class,
            EmployeeGroupRepository::class,
            EmployeeRepository::class,
            EmploymentProfileRepository::class,
            EmployeeContactRepository::class,
            EmployeePayrollComponentRepository::class,
            EmployeeShiftRepository::class,
            CompanyFormulaRepository::class,
            CompensationRepository::class,
            CompanyCompensationRepository::class,
            DeductionRepository::class,
            CompanyDeductionRepository::class,
            IncomeTaxRepository::class,
            CompanyIncomeTaxRepository::class,
            TimePeriodPresetRepository::class,
            SalaryStatementModuleRepository::class,
            ShiftRepository::class,
            ShiftScheduleRepository::class,
            PayFrequencyRepository::class,
        ];
    }
}
