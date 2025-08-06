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
use App\Blueprint\Repositories\EmployeePayrollComponentRepository;
use App\Blueprint\Repositories\EmployeeRepository;
use App\Blueprint\EnumInterface;
use App\Blueprint\Repositories\IncomeTaxRepository;
use App\Blueprint\Repositories\NonEmployeeUserRepository;
use App\Blueprint\Repositories\PayPeriodSettingRepository;
use App\Blueprint\Repositories\PrototypeRepository;
use App\Blueprint\Repositories\SalaryStatementModuleRepository;
use App\Blueprint\Repositories\TimePeriodPresetRepository;
use App\Blueprint\Repositories\UserCompanyAssignmentRepository;
use App\Blueprint\Repositories\UserRepository;
use App\Concrete\EnumConcrete;
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
use App\Concrete\Repositories\EmployeePayrollComponentRepositoryEloquent;
use App\Concrete\Repositories\EmployeeRepositoryEloquent;
use App\Concrete\Repositories\IncomeTaxRepositoryEloquent;
use App\Concrete\Repositories\NonEmployeeUserRepositoryEloquent;
use App\Concrete\Repositories\PayPeriodSettingRepositoryEloquent;
use App\Concrete\Repositories\PrototypeRepositoryEloquent;
use App\Concrete\Repositories\SalaryStatementModuleRepositoryEloquent;
use App\Concrete\Repositories\TimePeriodPresetRepositoryEloquent;
use App\Concrete\Repositories\UserCompanyAssignmentRepositoryEloquent;
use App\Concrete\Repositories\UserRepositoryEloquent;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class BindingsServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public $bindings = [
        'enum' => EnumConcrete::class,
        'account' => AccountRepositoryEloquent::class,
        'associated_account' => AssociatedAccountRepositoryEloquent::class,
        'user' => UserRepositoryEloquent::class,
        'non_employee_user' => NonEmployeeUserRepositoryEloquent::class,
        'user_company_assignment' => UserCompanyAssignmentRepositoryEloquent::class,
        'associated_user' => AssociatedUserRepositoryEloquent::class,
        'prototype' => PrototypeRepositoryEloquent::class,
        'company' => CompanyRepositoryEloquent::class,
        'associated_company' => AssociatedCompanyRepositoryEloquent::class,
        'designation' => DesignationRepositoryEloquent::class,
        'department' => DepartmentRepositoryEloquent::class,
        'employee' => EmployeeRepositoryEloquent::class,
        'employee_contact' => EmployeeContactRepositoryEloquent::class,
        'employee_payroll_component' => EmployeePayrollComponentRepositoryEloquent::class,
        'company_formula' => CompanyFormulaRepositoryEloquent::class,
        'compensation' => CompensationRepositoryEloquent::class,
        'company_compensation' => CompanyCompensationRepositoryEloquent::class,
        'deduction' => DeductionRepositoryEloquent::class,
        'company_deduction' => CompanyDeductionRepositoryEloquent::class,
        'income_tax' => IncomeTaxRepositoryEloquent::class,
        'company_income_tax' => CompanyIncomeTaxRepositoryEloquent::class,
        'pey_period_setting' => PayPeriodSettingRepositoryEloquent::class,
        'time_period_preset' => TimePeriodPresetRepositoryEloquent::class,
        'salary_statement_module' => SalaryStatementModuleRepositoryEloquent::class,
        EnumInterface::class => EnumConcrete::class,
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
        EmployeeRepository::class => EmployeeRepositoryEloquent::class,
        EmployeeContactRepository::class => EmployeeContactRepositoryEloquent::class,
        EmployeePayrollComponentRepository::class => EmployeePayrollComponentRepositoryEloquent::class,
        CompanyFormulaRepository::class => CompanyFormulaRepositoryEloquent::class,
        CompensationRepository::class => CompensationRepositoryEloquent::class,
        CompanyCompensationRepository::class => CompanyCompensationRepositoryEloquent::class,
        DeductionRepository::class => DeductionRepositoryEloquent::class,
        CompanyDeductionRepository::class => CompanyDeductionRepositoryEloquent::class,
        IncomeTaxRepository::class => IncomeTaxRepositoryEloquent::class,
        CompanyIncomeTaxRepository::class => CompanyIncomeTaxRepositoryEloquent::class,
        PayPeriodSettingRepository::class => PayPeriodSettingRepositoryEloquent::class,
        TimePeriodPresetRepository::class => TimePeriodPresetRepositoryEloquent::class,
        CompanyRepository::class => CompanyRepositoryEloquent::class,
        SalaryStatementModuleRepository::class => SalaryStatementModuleRepositoryEloquent::class,
    ];

    public function provides(): array
    {
        return [
            'enum',
            'account',
            'associated_account',
            'user',
            'non_employee_user',
            'user_company_assignment',
            'associated_user',
            'prototype',
            'company',
            'associated_company',
            'designation',
            'department',
            'employee',
            'employee_contact',
            'employee_payroll_component',
            'company_formula',
            'compensation',
            'company_compensation',
            'deduction',
            'company_deduction',
            'income_tax',
            'company_income_tax',
            'pey_period_setting',
            'time_period_preset',
            'salary_statement_module',
            EnumInterface::class,
            AccountRepository::class,
            AssociatedAccountRepository::class,
            UserRepository::class,
            NonEmployeeUserRepository::class,
            UserCompanyAssignmentRepository::class,
            AssociatedUserRepository::class,
            PrototypeRepository::class,
            CompanyRepository::class,
            AssociatedCompanyRepository::class,
            DesignationRepository::class,
            DepartmentRepository::class,
            EmployeeRepository::class,
            EmployeeContactRepository::class,
            EmployeePayrollComponentRepository::class,
            CompanyFormulaRepository::class,
            CompensationRepository::class,
            CompanyCompensationRepository::class,
            DeductionRepository::class,
            CompanyDeductionRepository::class,
            IncomeTaxRepository::class,
            CompanyIncomeTaxRepository::class,
            PayPeriodSettingRepository::class,
            TimePeriodPresetRepository::class,
            SalaryStatementModuleRepository::class,
        ];
    }
}
