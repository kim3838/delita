<?php

namespace App\Providers;

use App\Blueprint\Repositories\AccountRepository;
use App\Blueprint\Repositories\AccountSubscriptionRepository;
use App\Blueprint\Repositories\ApprovalSettingApproverRepository;
use App\Blueprint\Repositories\ApprovalSettingRepository;
use App\Blueprint\Repositories\AssociatedAccountRepository;
use App\Blueprint\Repositories\AssociatedCompanyRepository;
use App\Blueprint\Repositories\AssociatedUserRepository;
use App\Blueprint\Repositories\AttendanceAdjustmentRequestRepository;
use App\Blueprint\Repositories\AttendanceDetailRepository;
use App\Blueprint\Repositories\AttendanceRepository;
use App\Blueprint\Repositories\CompanyCompensationRepository;
use App\Blueprint\Repositories\CompanyDeductionRepository;
use App\Blueprint\Repositories\CompanyFormulaRepository;
use App\Blueprint\Repositories\CompanyIncomeTaxRepository;
use App\Blueprint\Repositories\CompanyRepository;
use App\Blueprint\Repositories\CompanyUserRepository;
use App\Blueprint\Repositories\CompanyUserRolePermissionRepository;
use App\Blueprint\Repositories\CompensationRepository;
use App\Blueprint\Repositories\DeductionRepository;
use App\Blueprint\Repositories\DepartmentRepository;
use App\Blueprint\Repositories\DesignationRepository;
use App\Blueprint\Repositories\EmployeeContactRepository;
use App\Blueprint\Repositories\EmployeeGroupRepository;
use App\Blueprint\Repositories\EmployeeLeaveTypeRepository;
use App\Blueprint\Repositories\EmployeePayrollComponentRepository;
use App\Blueprint\Repositories\EmployeeRepository;
use App\Blueprint\Repositories\EmployeeShiftRepository;
use App\Blueprint\Repositories\EmploymentProfileRepository;
use App\Blueprint\Repositories\FormulaRepository;
use App\Blueprint\Repositories\GroupRepository;
use App\Blueprint\Repositories\HolidayRepository;
use App\Blueprint\Repositories\IncomeTaxRepository;
use App\Blueprint\Repositories\JsonPresetRepository;
use App\Blueprint\Repositories\LeaveBalanceAdjustmentRepository;
use App\Blueprint\Repositories\LeaveRepository;
use App\Blueprint\Repositories\LeaveRequestRepository;
use App\Blueprint\Repositories\LeaveTypeBalancePerPeriodRepository;
use App\Blueprint\Repositories\LeaveTypeRepository;
use App\Blueprint\Repositories\NonEmployeeUserRepository;
use App\Blueprint\Repositories\OvertimeRepository;
use App\Blueprint\Repositories\OvertimeRequestRepository;
use App\Blueprint\Repositories\PayFrequencyRepository;
use App\Blueprint\Repositories\PayrollPayloadRepository;
use App\Blueprint\Repositories\PayrollRepository;
use App\Blueprint\Repositories\PermissionRepository;
use App\Blueprint\Repositories\PrototypeRepository;
use App\Blueprint\Repositories\RequestApprovalStateRepository;
use App\Blueprint\Repositories\RoleRepository;
use App\Blueprint\Repositories\SalaryStatementModuleRepository;
use App\Blueprint\Repositories\ShiftRepository;
use App\Blueprint\Repositories\ShiftScheduleRepository;
use App\Blueprint\Repositories\TimePeriodPresetRepository;
use App\Blueprint\Repositories\UserCompanyAssignmentRepository;
use App\Blueprint\Repositories\UserFiledRequestRepository;
use App\Blueprint\Repositories\UserRepository;
use App\Concrete\Repositories\AccountRepositoryEloquent;
use App\Concrete\Repositories\AccountSubscriptionRepositoryEloquent;
use App\Concrete\Repositories\ApprovalSettingApproverRepositoryEloquent;
use App\Concrete\Repositories\ApprovalSettingRepositoryEloquent;
use App\Concrete\Repositories\AssociatedAccountRepositoryEloquent;
use App\Concrete\Repositories\AssociatedCompanyRepositoryEloquent;
use App\Concrete\Repositories\AssociatedUserRepositoryEloquent;
use App\Concrete\Repositories\AttendanceAdjustmentRequestRepositoryEloquent;
use App\Concrete\Repositories\AttendanceDetailRepositoryEloquent;
use App\Concrete\Repositories\AttendanceRepositoryEloquent;
use App\Concrete\Repositories\CompanyCompensationRepositoryEloquent;
use App\Concrete\Repositories\CompanyDeductionRepositoryEloquent;
use App\Concrete\Repositories\CompanyFormulaRepositoryEloquent;
use App\Concrete\Repositories\CompanyIncomeTaxRepositoryEloquent;
use App\Concrete\Repositories\CompanyRepositoryEloquent;
use App\Concrete\Repositories\CompanyUserRepositoryEloquent;
use App\Concrete\Repositories\CompanyUserRolePermissionRepositoryEloquent;
use App\Concrete\Repositories\CompensationRepositoryEloquent;
use App\Concrete\Repositories\DeductionRepositoryEloquent;
use App\Concrete\Repositories\DepartmentRepositoryEloquent;
use App\Concrete\Repositories\DesignationRepositoryEloquent;
use App\Concrete\Repositories\EmployeeContactRepositoryEloquent;
use App\Concrete\Repositories\EmployeeGroupRepositoryEloquent;
use App\Concrete\Repositories\EmployeeLeaveTypeRepositoryEloquent;
use App\Concrete\Repositories\EmployeePayrollComponentRepositoryEloquent;
use App\Concrete\Repositories\EmployeeRepositoryEloquent;
use App\Concrete\Repositories\EmployeeShiftRepositoryEloquent;
use App\Concrete\Repositories\EmploymentProfileRepositoryEloquent;
use App\Concrete\Repositories\FormulaRepositoryEloquent;
use App\Concrete\Repositories\GroupRepositoryEloquent;
use App\Concrete\Repositories\HolidayRepositoryEloquent;
use App\Concrete\Repositories\IncomeTaxRepositoryEloquent;
use App\Concrete\Repositories\JsonPresetRepositoryEloquent;
use App\Concrete\Repositories\LeaveBalanceAdjustmentRepositoryEloquent;
use App\Concrete\Repositories\LeaveRepositoryEloquent;
use App\Concrete\Repositories\LeaveRequestRepositoryEloquent;
use App\Concrete\Repositories\LeaveTypeBalancePerPeriodRepositoryEloquent;
use App\Concrete\Repositories\LeaveTypeRepositoryEloquent;
use App\Concrete\Repositories\NonEmployeeUserRepositoryEloquent;
use App\Concrete\Repositories\OvertimeRepositoryEloquent;
use App\Concrete\Repositories\OvertimeRequestRepositoryEloquent;
use App\Concrete\Repositories\PayFrequencyRepositoryEloquent;
use App\Concrete\Repositories\PayrollPayloadRepositoryEloquent;
use App\Concrete\Repositories\PayrollRepositoryEloquent;
use App\Concrete\Repositories\PermissionRepositoryEloquent;
use App\Concrete\Repositories\PrototypeRepositoryEloquent;
use App\Concrete\Repositories\RequestApprovalStateRepositoryEloquent;
use App\Concrete\Repositories\RoleRepositoryEloquent;
use App\Concrete\Repositories\SalaryStatementModuleRepositoryEloquent;
use App\Concrete\Repositories\ShiftRepositoryEloquent;
use App\Concrete\Repositories\ShiftScheduleRepositoryEloquent;
use App\Concrete\Repositories\TimePeriodPresetRepositoryEloquent;
use App\Concrete\Repositories\UserCompanyAssignmentRepositoryEloquent;
use App\Concrete\Repositories\UserFiledRequestRepositoryEloquent;
use App\Concrete\Repositories\UserRepositoryEloquent;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class RepositoryBindingsServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public $bindings = [
        'account' => AccountRepositoryEloquent::class,
        'account_subscription' => AccountSubscriptionRepositoryEloquent::class,
        'associated_account' => AssociatedAccountRepositoryEloquent::class,
        'user' => UserRepositoryEloquent::class,
        'company_user' => CompanyUserRepositoryEloquent::class,
        'role' => RoleRepositoryEloquent::class,
        'company_user_role_permission' => CompanyUserRolePermissionRepositoryEloquent::class,
        'permission' => PermissionRepositoryEloquent::class,
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
        'employee_leave_type' => EmployeeLeaveTypeRepositoryEloquent::class,
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
        'attendance' => AttendanceRepositoryEloquent::class,
        'attendance_detail' => AttendanceDetailRepositoryEloquent::class,
        'overtime' => OvertimeRepositoryEloquent::class,
        'holiday' => HolidayRepositoryEloquent::class,
        'leave_type' => LeaveTypeRepositoryEloquent::class,
        'leave' => LeaveRepositoryEloquent::class,
        'leave_type_balance_per_period' => LeaveTypeBalancePerPeriodRepositoryEloquent::class,
        'leave_balance_adjustment' => LeaveBalanceAdjustmentRepositoryEloquent::class,
        'approval_setting' => ApprovalSettingRepositoryEloquent::class,
        'approval_setting_approver' => ApprovalSettingApproverRepositoryEloquent::class,
        'request_approval_state' => RequestApprovalStateRepositoryEloquent::class,
        'attendance_adjustment_request' => AttendanceAdjustmentRequestRepositoryEloquent::class,
        'overtime_request' => OvertimeRequestRepositoryEloquent::class,
        'leave_request' => LeaveRequestRepositoryEloquent::class,
        'user_filed_request' => UserFiledRequestRepositoryEloquent::class,
        'payroll' => PayrollRepositoryEloquent::class,
        'payroll_payload' => PayrollPayloadRepositoryEloquent::class,
        AccountRepository::class => AccountRepositoryEloquent::class,
        AccountSubscriptionRepository::class => AccountSubscriptionRepositoryEloquent::class,
        AssociatedAccountRepository::class => AssociatedAccountRepositoryEloquent::class,
        UserRepository::class => UserRepositoryEloquent::class,
        CompanyUserRepository::class => CompanyUserRepositoryEloquent::class,
        RoleRepository::class => RoleRepositoryEloquent::class,
        CompanyUserRolePermissionRepository::class => CompanyUserRolePermissionRepositoryEloquent::class,
        PermissionRepository::class => PermissionRepositoryEloquent::class,
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
        EmployeeLeaveTypeRepository::class => EmployeeLeaveTypeRepositoryEloquent::class,
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
        AttendanceRepository::class => AttendanceRepositoryEloquent::class,
        AttendanceDetailRepository::class => AttendanceDetailRepositoryEloquent::class,
        OvertimeRepository::class => OvertimeRepositoryEloquent::class,
        HolidayRepository::class => HolidayRepositoryEloquent::class,
        LeaveTypeRepository::class => LeaveTypeRepositoryEloquent::class,
        LeaveRepository::class => LeaveRepositoryEloquent::class,
        LeaveTypeBalancePerPeriodRepository::class => LeaveTypeBalancePerPeriodRepositoryEloquent::class,
        LeaveBalanceAdjustmentRepository::class => LeaveBalanceAdjustmentRepositoryEloquent::class,
        ApprovalSettingRepository::class => ApprovalSettingRepositoryEloquent::class,
        ApprovalSettingApproverRepository::class => ApprovalSettingApproverRepositoryEloquent::class,
        RequestApprovalStateRepository::class => RequestApprovalStateRepositoryEloquent::class,
        AttendanceAdjustmentRequestRepository::class => AttendanceAdjustmentRequestRepositoryEloquent::class,
        OvertimeRequestRepository::class => OvertimeRequestRepositoryEloquent::class,
        LeaveRequestRepository::class => LeaveRequestRepositoryEloquent::class,
        UserFiledRequestRepository::class => UserFiledRequestRepositoryEloquent::class,
        PayrollRepository::class => PayrollRepositoryEloquent::class,
        PayrollPayloadRepository::class => PayrollPayloadRepositoryEloquent::class,
    ];

    public function provides(): array
    {
        return [
            'account',
            'account_subscription',
            'associated_account',
            'user',
            'company_user',
            'role',
            'company_user_role_permission',
            'permission',
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
            'employee_leave_type',
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
            'attendance',
            'attendance_detail',
            'overtime',
            'holiday',
            'leave_type',
            'leave',
            'leave_type_balance_per_period',
            'leave_balance_adjustment',
            'approval_setting',
            'approval_setting_approver',
            'request_approval_state',
            'attendance_adjustment_request',
            'overtime_request',
            'leave_request',
            'user_filed_request',
            'payroll',
            'payroll_payload',
            AccountRepository::class,
            AccountSubscriptionRepository::class,
            AssociatedAccountRepository::class,
            UserRepository::class,
            CompanyUserRepository::class,
            RoleRepository::class,
            CompanyUserRolePermissionRepository::class,
            PermissionRepository::class,
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
            EmployeeLeaveTypeRepository::class,
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
            AttendanceRepository::class,
            AttendanceDetailRepository::class,
            OvertimeRepository::class,
            HolidayRepository::class,
            LeaveTypeRepository::class,
            LeaveRepository::class,
            LeaveTypeBalancePerPeriodRepository::class,
            LeaveBalanceAdjustmentRepository::class,
            ApprovalSettingRepository::class,
            ApprovalSettingApproverRepository::class,
            RequestApprovalStateRepository::class,
            AttendanceAdjustmentRequestRepository::class,
            OvertimeRequestRepository::class,
            LeaveRequestRepository::class,
            UserFiledRequestRepository::class,
            PayrollRepository::class,
            PayrollPayloadRepository::class,
        ];
    }
}
