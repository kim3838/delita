<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\ApprovalSettingController;
use App\Http\Controllers\AssociatedAccountController;
use App\Http\Controllers\AssociatedCompanyController;
use App\Http\Controllers\AssociatedUserController;
use App\Http\Controllers\AttendanceAdjustmentRequestController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceImportTemplateController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmedTwoFactorAuthenticationController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\TwoFactorAuthenticatedSessionController;
use App\Http\Controllers\Auth\TwoFactorAuthenticationController;
use App\Http\Controllers\Auth\TwoFactorQrCodeController;
use App\Http\Controllers\Auth\TwoFactorRecoveryCodeController;
use App\Http\Controllers\Auth\TwoFactorSecretKeyController;
use App\Http\Controllers\Auth\UpdateUserPasswordController;
use App\Http\Controllers\AutoCreateAttendanceController;
use App\Http\Controllers\BulkOrganizationController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CompanyUserController;
use App\Http\Controllers\CompanyUserRolePermissionController;
use App\Http\Controllers\CompensationController;
use App\Http\Controllers\CompanyFormulaController;
use App\Http\Controllers\DeductionController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\EmployeeContactController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeEmploymentProfilesController;
use App\Http\Controllers\EmployeeIdentificationController;
use App\Http\Controllers\EmployeeIdentificationTemplateController;
use App\Http\Controllers\EmployeeImportTemplateController;
use App\Http\Controllers\EmployeeLeaveTypeController;
use App\Http\Controllers\EmployeePayrollComponentController;
use App\Http\Controllers\EmployeePayItemsImportTemplateController;
use App\Http\Controllers\EmployeePayrollInfoController;
use App\Http\Controllers\EmployeePortal\AttendanceAdjustmentRequestController as EmployeePortalAttendanceAdjustmentRequestController;
use App\Http\Controllers\EmployeePortal\AttendanceController as EmployeePortalAttendanceController;
use App\Http\Controllers\EmployeePortal\EmployeeController as EmployeePortalEmployeeController;
use App\Http\Controllers\EmployeePortal\LeaveBalanceAdjustmentController as EmployeePortalLeaveBalanceAdjustmentController;
use App\Http\Controllers\EmployeePortal\LeaveController as EmployeePortalLeaveController;
use App\Http\Controllers\EmployeePortal\LeaveRequestController as EmployeePortalLeaveRequestController;
use App\Http\Controllers\EmployeePortal\LeaveRunningBalanceByTypeController as EmployeePortalLeaveRunningBalanceByTypeController;
use App\Http\Controllers\EmployeePortal\OvertimeController as EmployeePortalOvertimeController;
use App\Http\Controllers\EmployeePortal\OvertimeRequestController as EmployeePortalOvertimeRequestController;
use App\Http\Controllers\EmployeePortal\PayrollAttendanceController as EmployeePortalPayrollAttendanceController;
use App\Http\Controllers\EmployeeSalaryStatementController as EmployeeSalaryStatementController;
use App\Http\Controllers\EmployeeShiftController;
use App\Http\Controllers\EmploymentProfileController;
use App\Http\Controllers\EmploymentProfileImportTemplateController;
use App\Http\Controllers\EnumController;
use App\Http\Controllers\FormModuleController;
use App\Http\Controllers\FormulaController;
use App\Http\Controllers\EmployeeGroupController;
use App\Http\Controllers\Imports\EmployeeIdentificationController as EmployeeIdentificationImportController;
use App\Http\Controllers\Internal\TaxCalculatorController;
use App\Http\Controllers\PayrollRequestController;
use App\Http\Controllers\PreGeneratePayrollController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\Imports\AttendanceController as AttendanceImportController;
use App\Http\Controllers\Imports\EmployeeController as EmployeeImportController;
use App\Http\Controllers\Imports\EmployeePayItemsController as EmployeePayItemsImportController;
use App\Http\Controllers\Imports\EmploymentProfileController as EmploymentProfileImportController;
use App\Http\Controllers\Imports\OvertimeController as OvertimeImportController;
use App\Http\Controllers\IncomeTaxController;
use App\Http\Controllers\Internal\UtilityController;
use App\Http\Controllers\JsonController;
use App\Http\Controllers\JsonPresetController;
use App\Http\Controllers\LeaveBalanceAdjustmentController;
use App\Http\Controllers\LeaveDateRangeInquireController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\LeaveRunningBalanceByTypeController;
use App\Http\Controllers\LeaveRunningBalanceController;
use App\Http\Controllers\LeaveRunningBalancePeriodSeriesController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\LeaveDateRangeFilterController;
use App\Http\Controllers\LeaveTypeController;
use App\Http\Controllers\NonEmployeeUserController;
use App\Http\Controllers\OrderableController;
use App\Http\Controllers\OvertimeController;
use App\Http\Controllers\OvertimeImportTemplateController;
use App\Http\Controllers\OvertimeRequestController;
use App\Http\Controllers\PayFrequencyController;
use App\Http\Controllers\PayrollComponentController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\PayrollInquiryController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RequestApprovalStateController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SalaryStatementAttendanceController;
use App\Http\Controllers\SalaryStatementController;
use App\Http\Controllers\TimePeriodPresetController;
use App\Http\Controllers\PrototypeController;
use App\Http\Controllers\SalaryStatementModuleController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\ShiftScheduleController;
use App\Http\Controllers\UserFiledRequestController;
use App\Http\Controllers\UserRequestApprovalStateController;
use App\Http\Controllers\WorldController;
use App\Http\Controllers\UserCompanyAssignmentController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

if(true || App::environment('local', 'development')) {
    Route::group([
        'prefix' => 'utility'
    ], function(){

        Route::post('debug', [UtilityController::class, 'debug']);

        //Verify X-XSRF-TOKEN on destructive action
        Route::post('post', [UtilityController::class, 'post']);
    });
}

Route::get('timezone-selections', [WorldController::class, 'timezoneSelection']);
Route::get('country-selections', [WorldController::class, 'countrySelection']);
Route::get('currency-selections', [WorldController::class, 'currencySelection']);

//Import templates
Route::get('employee-import-template', [EmployeeImportTemplateController::class, 'index']);
Route::get('employment-profile-import-template', [EmploymentProfileImportTemplateController::class, 'index']);
Route::get('employee-pay-items-import-template', [EmployeePayItemsImportTemplateController::class, 'index']);
Route::get('attendance-import-template', [AttendanceImportTemplateController::class, 'index']);
Route::get('overtime-import-template', [OvertimeImportTemplateController::class, 'index']);
Route::get('employee-identification-import-template', [EmployeeIdentificationTemplateController::class, 'index']);

Route::post('monthly-salary-calculate-tax', [TaxCalculatorController::class, 'store']);

Route::group([
    'middleware' => ['guest']
], function(){
    Route::post('login', [AuthenticatedSessionController::class, 'store'])->name('login');
    Route::post('two-factor-login', [TwoFactorAuthenticatedSessionController::class, 'store'])->name('two-factor.login');
});

Route::group([
    'middleware' => ['auth:sanctum'],
    'prefix' => 'utility'
], function(){

    //Hit server
    Route::get('hit', [UtilityController::class, 'hit']);
});

Route::group([
    'middleware' => ['auth:sanctum']
], function(){

    //Common
    Route::post('orderable/re-order/{module}', [OrderableController::class, 'reOrder']);
    Route::post('read-json-file', [JsonController::class, 'read']);
    Route::get('model-selections/{module}', [FormModuleController::class, 'selection'])->name('selection');
    Route::get('enum-selections/{enum}', [EnumController::class, 'selection'])->name('enum.selection');
    Route::get('payroll-component-pay-selections', [EnumController::class, 'payrollComponentPaySelections']);

    //JSON Preset
    Route::get('json-presets', [JsonPresetController::class, 'index']);
    Route::get('json-preset-gate/{jsonPresetId}', [JsonPresetController::class, 'showGate']);
    Route::post('json-preset', [JsonPresetController::class, 'store']);
    Route::get('json-preset/{jsonPresetId}', [JsonPresetController::class, 'show']);
    Route::get('json-preset-download/{jsonPresetId}', [JsonPresetController::class, 'download']);
    Route::patch('json-preset/{jsonPresetId}', [JsonPresetController::class, 'update']);
    Route::delete('json-preset/{jsonPresetId}', [JsonPresetController::class, 'destroy']);

    //Time Period Preset Selection
    Route::get('time-period-preset-selections', [TimePeriodPresetController::class, 'selection']);

    //User authentication
    Route::get('user', [AuthenticatedSessionController::class, 'authenticated']);
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::post('update-password', [UpdateUserPasswordController::class, 'store'])->name('password.update');
    Route::get('confirmed-password-status', [AuthenticatedSessionController::class, 'confirmedPasswordStatus'])->name('password.confirmation');
    Route::post('confirm-password', [AuthenticatedSessionController::class, 'confirmPassword'])->name('password.confirm');
    Route::post('logout-other-device', [AuthenticatedSessionController::class, 'logoutOtherDevice']);
    Route::get('sessions', [AuthenticatedSessionController::class, 'sessions']);

    //Account
    Route::get('accounts', [AccountController::class, 'index']);
    Route::get('account-selections', [AccountController::class, 'selection']);
    Route::get('account/{ulid}', [AccountController::class, 'show']);
    Route::get('account-gate/{ulid}', [AccountController::class, 'showGate']);
    Route::post('account', [AccountController::class, 'store']);
    Route::patch('account/{accountId}', [AccountController::class, 'update']);

    //Role
    Route::get('roles', [RoleController::class, 'index']);
    Route::get('role-selections', [RoleController::class, 'selection']);
    Route::get('role-permission-template', [RoleController::class, 'permissionTemplate']);
    Route::get('role/{ulid}', [RoleController::class, 'show']);
    Route::post('role', [RoleController::class, 'store']);
    Route::patch('role/{ulid}', [RoleController::class, 'update']);
    Route::delete('roles', [RoleController::class, 'batchDestroy']);

    //Permission
    Route::get('permission-series', [PermissionController::class, 'series']);

    //User
    Route::get('users', [UserController::class, 'index']);
    Route::get('users-gate', [UserController::class, 'indexGate']);
    Route::get('user/{ulid}', [UserController::class, 'show']);
    Route::get('user-gate/{ulid}', [UserController::class, 'showGate']);
    Route::post('user-validate', [UserController::class, 'validate']);
    Route::post('user', [UserController::class, 'store']);
    Route::patch('user/{userId}', [UserController::class, 'update']);

    Route::post('autogenerate-user-validate', [UserController::class, 'autoGenerateValidate']);
    Route::post('autogenerate-user', [UserController::class, 'autoGenerate']);

    Route::get('non-employee-user-selections', [NonEmployeeUserController::class, 'selection']);

    //Email verification
    Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])->name('verification.send');

    //Two-factor authentication
    Route::post('two-factor-authentication', [TwoFactorAuthenticationController::class, 'store'])->name('two-factor.enable');
    Route::get('two-factor-qr-code', [TwoFactorQrCodeController::class, 'show'])->name('two-factor.qr-code');
    Route::get('two-factor-secret-key', [TwoFactorSecretKeyController::class, 'show'])->name('two-factor.secret-key');
    Route::get('two-factor-recovery-codes', [TwoFactorRecoveryCodeController::class, 'index'])->name('two-factor.recovery-codes');
    Route::post('confirmed-two-factor-authentication', [ConfirmedTwoFactorAuthenticationController::class, 'store'])->name('two-factor.confirm');
    Route::delete('two-factor-authentication', [TwoFactorAuthenticationController::class, 'destroy']);

    //Formula
    Route::get('formulas', [FormulaController::class, 'index']);
    Route::get('formula-selections', [FormulaController::class, 'selection']);
    Route::get('formula-gate/{ulid}', [FormulaController::class, 'showGate']);
    Route::get('formula/{ulid}', [FormulaController::class, 'show']);
    Route::post('formula', [FormulaController::class, 'store']);
    Route::patch('formula/{formulaId}', [FormulaController::class, 'update']);
    Route::delete('formula/{formulaId}', [FormulaController::class, 'destroy']);

    //Company
    Route::get('companies', [CompanyController::class, 'index']);
    Route::get('company-selections', [CompanyController::class, 'selection']);
    Route::get('company/{ulid}', [CompanyController::class, 'show']);
    Route::get('company-gate/{ulid}', [CompanyController::class, 'showGate']);
    Route::post('company', [CompanyController::class, 'store']);
    Route::patch('company/{companyId}', [CompanyController::class, 'update']);

    Route::get('organization-selections', [BulkOrganizationController::class, 'index']);

    //Company Formula
    Route::get('company-formula-selections', [CompanyFormulaController::class, 'selection']);
    Route::get('company-formula/{companyFormulaId}', [CompanyFormulaController::class, 'show']);
    Route::get('company-formulas', [CompanyFormulaController::class, 'index']);
    Route::post('company-formula-assignment-sync/{companyFormulaId}', [CompanyFormulaController::class, 'sync']);
    Route::post('company-formula-assignment-sync-without-detaching/{companyFormulaId}', [CompanyFormulaController::class, 'syncWithoutDetaching']);

    //Departments
    Route::get('departments', [DepartmentController::class, 'index']);
    Route::get('departments-gate', [DepartmentController::class, 'indexGate']);
    Route::get('department-selections', [DepartmentController::class, 'selection']);
    Route::post('department', [DepartmentController::class, 'store']);
    Route::patch('department/{departmentId}', [DepartmentController::class, 'update']);
    Route::delete('department/{departmentId}', [DepartmentController::class, 'destroy']);

    //Designations
    Route::get('designations', [DesignationController::class, 'index']);
    Route::get('designations-gate', [DesignationController::class, 'indexGate']);
    Route::get('designation-selections', [DesignationController::class, 'selection']);
    Route::post('designation', [DesignationController::class, 'store']);
    Route::patch('designation/{designationId}', [DesignationController::class, 'update']);
    Route::delete('designation/{designationId}', [DesignationController::class, 'destroy']);

    //User association
    Route::get('user-is-admin-in-any-company', [AuthenticatedSessionController::class, 'isAdminInAnyCompany']);
    Route::get('associated-accounts', [AssociatedAccountController::class, 'index']);
    Route::get('associated-account-selections', [AssociatedAccountController::class, 'selection']);
    Route::get('associated-users', [AssociatedUserController::class, 'index']);

    Route::get('company-users', [CompanyUserController::class, 'index']);
    Route::get('company-user-selections', [CompanyUserController::class, 'selection']);

    //Company User Role Permission
    Route::get('company-user-role-permissions', [CompanyUserRolePermissionController::class, 'index']);

    Route::get('associated-users-gate', [AssociatedUserController::class, 'indexGate']);
    Route::get('associated-companies', [AssociatedCompanyController::class, 'index']);
    Route::get('associated-companies-gate', [AssociatedCompanyController::class, 'indexGate']);
    Route::get('associated-company/{ulid}', [AssociatedCompanyController::class, 'show']);
    Route::patch('associated-company/{companyId}', [AssociatedCompanyController::class, 'update']);
    Route::get('associated-company-selections', [AssociatedCompanyController::class, 'selection']);

    //User-Company Assignment
    Route::get('user-company-assignment', [UserCompanyAssignmentController::class, 'index']);
    Route::post('user-company-assignment-sync/{userId}', [UserCompanyAssignmentController::class, 'sync']);

    //Pay Frequencies
    Route::get('pay-frequencies', [PayFrequencyController::class, 'index']);
    Route::get('pay-frequencies-gate', [PayFrequencyController::class, 'indexGate']);
    Route::get('pay-frequency-selections', [PayFrequencyController::class, 'selection']);
    Route::patch('pay-frequency/{payFrequencyId}', [PayFrequencyController::class, 'update']);

    //Approval Settings
    Route::get('approval-settings', [ApprovalSettingController::class, 'index']);
    Route::get('approval-settings-gate', [ApprovalSettingController::class, 'indexGate']);
    Route::patch('approval-setting/{approvalSettingId}', [ApprovalSettingController::class, 'update']);

    //Approval States
    Route::get('approval-states', [RequestApprovalStateController::class, 'index']);
    //Apply approval workflow
    Route::post('approval-states-workflow', [RequestApprovalStateController::class, 'applyWorkflow']);

    //Approval States: that are only to be approved by the user
    Route::get('user-approval-states', [UserRequestApprovalStateController::class, 'index']);

    //Requests (Attendance adjustments) that requested by the user
    Route::get('user-filed-requests', [UserFiledRequestController::class, 'index']);
    Route::delete('user-filed-requests', [UserFiledRequestController::class, 'batchDestroy']);

    //Payroll Component (compensation, deduction, income tax)
    Route::get('payroll-components-gate', [PayrollComponentController::class, 'indexGate']);

    //Compensation
    Route::get('compensations', [CompensationController::class, 'index']);
    Route::get('compensation-selections', [CompensationController::class, 'selection']);
    Route::post('compensation', [CompensationController::class, 'store']);
    Route::patch('compensation/{compensationId}', [CompensationController::class, 'update']);
    Route::delete('compensation/{compensationId}', [CompensationController::class, 'destroy']);
    Route::delete('compensations', [CompensationController::class, 'batchDestroy']);

    //Deduction
    Route::get('deductions', [DeductionController::class, 'index']);
    Route::get('deduction-selections', [DeductionController::class, 'selection']);
    Route::post('deduction', [DeductionController::class, 'store']);
    Route::patch('deduction/{deductionId}', [DeductionController::class, 'update']);
    Route::delete('deduction/{deductionId}', [DeductionController::class, 'destroy']);
    Route::delete('deductions', [DeductionController::class, 'batchDestroy']);

    //Income Tax
    Route::get('income-taxes', [IncomeTaxController::class, 'index']);
    Route::get('income-tax-selections', [IncomeTaxController::class, 'selection']);
    Route::post('income-tax', [IncomeTaxController::class, 'store']);
    Route::patch('income-tax/{incomeTaxId}', [IncomeTaxController::class, 'update']);
    Route::delete('income-tax/{incomeTaxId}', [IncomeTaxController::class, 'destroy']);
    Route::delete('income-taxes', [IncomeTaxController::class, 'batchDestroy']);

    //Salary Statement Modules
    Route::get('salary-statement-modules', [SalaryStatementModuleController::class, 'index']);
    Route::post('salary-statement-module', [SalaryStatementModuleController::class, 'store']);
    Route::patch('salary-statement-module/{salaryStatementModuleId}', [SalaryStatementModuleController::class, 'update']);
    Route::delete('salary-statement-module/{salaryStatementModuleId}', [SalaryStatementModuleController::class, 'destroy']);
    Route::post('re-order/salary-statement-modules', [SalaryStatementModuleController::class, 'reOrder']);

    //Employees
    Route::get('employees', [EmployeeController::class, 'index']);
    Route::get('employees-gate', [EmployeeController::class, 'indexGate']);
    Route::post('employee-validate', [EmployeeController::class, 'validate']);
    Route::post('employee', [EmployeeController::class, 'store']);
    Route::patch('employee/{employeeId}', [EmployeeController::class, 'update']);
    Route::patch('employees', [EmployeeController::class, 'batchUpdate']);
    Route::get('employee-selections', [EmployeeController::class, 'selection']);
    Route::get('employee/{ulid}', [EmployeeController::class, 'show']);
    Route::get('employee-gate/{ulid}', [EmployeeController::class, 'showGate']);
    Route::post('employee-import-validate', [EmployeeImportController::class, 'read']);
    Route::post('employee-import-re-validate', [EmployeeImportController::class, 'reValidate']);
    Route::post('employee-import-save', [EmployeeImportController::class, 'save']);

    Route::get('employee-portal-employees', [EmployeePortalEmployeeController::class, 'index']);
    Route::get('employee-portal-employee-gate/{ulid}', [EmployeePortalEmployeeController::class, 'showGate']);
    Route::get('employee-portal-employee/{ulid}', [EmployeePortalEmployeeController::class, 'show']);

    //Employee Groups
    Route::get('employee-groups', [EmployeeGroupController::class, 'index']);
    Route::get('employee-groups-gate', [EmployeeGroupController::class, 'indexGate']);
    Route::get('employee-group-selections', [EmployeeGroupController::class, 'selection']);
    Route::post('employee-group', [EmployeeGroupController::class, 'store']);
    Route::patch('employee-group/{ulid}', [EmployeeGroupController::class, 'update']);

    Route::post('employee-group-assignment-sync-without-detaching', [EmployeeGroupController::class, 'syncWithoutDetaching']);
    Route::post('employee-group-assignment-detach', [EmployeeGroupController::class, 'detach']);
    Route::delete('employee-groups', [EmployeeGroupController::class, 'batchDestroy']);

    //Employee Contact
    Route::get('employee-contact/{employeeId}', [EmployeeContactController::class, 'show']);
    Route::post('employee-contact-validate', [EmployeeContactController::class, 'validate']);
    Route::post('employee-contact', [EmployeeContactController::class, 'store']);
    Route::patch('employee-contact/{employeeId}', [EmployeeContactController::class, 'update']);

    //Employee Employment Profiles
    Route::get('employee-employment-profiles/{employeeId}', [EmployeeEmploymentProfilesController::class, 'index']);

    //Employment Profile
    Route::get('employment-profiles', [EmploymentProfileController::class, 'index']);
    Route::get('employment-profiles-gate', [EmploymentProfileController::class, 'indexGate']);
    Route::post('employment-profile-validate', [EmploymentProfileController::class, 'validate']);
    Route::post('employment-profile', [EmploymentProfileController::class, 'store']);
    Route::patch('employment-profile/{employmentProfileId}', [EmploymentProfileController::class, 'update']);
    Route::delete('employment-profile/{employmentProfileId}', [EmploymentProfileController::class, 'destroy']);
    Route::delete('employment-profiles', [EmploymentProfileController::class, 'batchDestroy']);
    Route::post('employment-profile-import-validate', [EmploymentProfileImportController::class, 'read']);
    Route::post('employment-profile-import-re-validate', [EmploymentProfileImportController::class, 'reValidate']);
    Route::post('employment-profile-import-save', [EmploymentProfileImportController::class, 'save']);

    //Employee Identification
    Route::get('employee-identifications', [EmployeeIdentificationController::class, 'index']);
    Route::get('employee-identifications-gate', [EmployeeIdentificationController::class, 'indexGate']);
    Route::post('employee-identification-validate', [EmployeeIdentificationController::class, 'validate']);
    Route::post('employee-identification', [EmployeeIdentificationController::class, 'store']);
    Route::patch('employee-identification/{employeeIdentificationId}', [EmployeeIdentificationController::class, 'update']);
    Route::delete('employee-identifications', [EmployeeIdentificationController::class, 'batchDestroy']);

    Route::post('employee-identification-import-validate', [EmployeeIdentificationImportController::class, 'read']);
    Route::post('employee-identification-import-re-validate', [EmployeeIdentificationImportController::class, 'reValidate']);
    Route::post('employee-identification-import-save', [EmployeeIdentificationImportController::class, 'save']);

    //Employee Payroll Component
    Route::get('employee-payroll-components', [EmployeePayrollComponentController::class, 'index']);
    Route::get('employee-payroll-components-gate', [EmployeePayrollComponentController::class, 'indexGate']);
    Route::get('employee-payroll-component-name-selections', [EmployeePayrollComponentController::class, 'payrollComponentName']);
    Route::get('employee-payroll-component-type-selections', [EmployeePayrollComponentController::class, 'payrollComponentType']);
    Route::post('employee-payroll-component-validate', [EmployeePayrollComponentController::class, 'validate']);
    Route::post('employee-payroll-component', [EmployeePayrollComponentController::class, 'store']);
    Route::patch('employee-payroll-component/{employeePayrollComponentId}', [EmployeePayrollComponentController::class, 'update']);
    Route::delete('employee-payroll-component/{employeePayrollComponentId}', [EmployeePayrollComponentController::class, 'destroy']);
    Route::delete('employee-payroll-components', [EmployeePayrollComponentController::class, 'batchDestroy']);
    Route::post('employee-pay-items-import-validate', [EmployeePayItemsImportController::class, 'read']);
    Route::post('employee-pay-items-import-re-validate', [EmployeePayItemsImportController::class, 'reValidate']);
    Route::post('employee-pay-items-import-save', [EmployeePayItemsImportController::class, 'save']);

    Route::get('employee-payroll-info/{employeeUlid}/compensations', [EmployeePayrollInfoController::class, 'compensations']);
    Route::get('employee-payroll-info/{employeeUlid}/deductions', [EmployeePayrollInfoController::class, 'deductions']);
    Route::get('employee-payroll-info/{employeeUlid}/income-taxes', [EmployeePayrollInfoController::class, 'incomeTaxes']);

    //Attendance
    Route::get('attendances', [AttendanceController::class, 'index']);
    Route::get('attendances-gate', [AttendanceController::class, 'indexGate']);
    Route::get('attendance/{attendanceUlid}', [AttendanceController::class, 'show']);
    Route::get('attendance-gate/{attendanceUlid}', [AttendanceController::class, 'showGate']);
    Route::patch('attendance/{attendanceUlid}', [AttendanceController::class, 'update']);
    Route::delete('attendances', [AttendanceController::class, 'batchDestroy']);
    Route::post('attendance-import-validate', [AttendanceImportController::class, 'read']);
    Route::post('attendance-import-re-validate', [AttendanceImportController::class, 'reValidate']);
    Route::post('attendance-import-save', [AttendanceImportController::class, 'save']);

    Route::get('employee-portal-attendances', [EmployeePortalAttendanceController::class, 'index']);
    Route::get('employee-portal-attendance/{attendanceUlid}', [EmployeePortalAttendanceController::class, 'show']);
    Route::get('employee-portal-attendances-gate/{attendanceUlid}', [EmployeePortalAttendanceController::class, 'showGate']);

    Route::get('employee-portal-payroll-attendances', [EmployeePortalPayrollAttendanceController::class, 'index']);

    //Auto create attendance
    Route::post('auto-create-attendances', [AutoCreateAttendanceController::class, 'store']);

    //Attendance Adjustment Request
    Route::get('attendance-adjustment-requests', [AttendanceAdjustmentRequestController::class, 'index']);
    Route::get('attendance-adjustment-request/{requestNumber}', [AttendanceAdjustmentRequestController::class, 'show']);
    Route::post('attendance-adjustment-request', [AttendanceAdjustmentRequestController::class, 'store']);
    Route::delete('attendance-adjustment-requests', [AttendanceAdjustmentRequestController::class, 'batchDestroy']);

    Route::post('employee-portal-attendance-adjustment-request', [EmployeePortalAttendanceAdjustmentRequestController::class, 'store']);

    //Overtime
    Route::get('overtimes', [OvertimeController::class, 'index']);
    Route::get('overtimes-gate', [OvertimeController::class, 'indexGate']);
    Route::patch('overtime/{overtimeUlid}', [OvertimeController::class, 'update']);
    Route::post('overtime', [OvertimeController::class, 'store']);
    Route::delete('overtimes', [OvertimeController::class, 'batchDestroy']);
    Route::post('overtime-import-validate', [OvertimeImportController::class, 'read']);
    Route::post('overtime-import-re-validate', [OvertimeImportController::class, 'reValidate']);
    Route::post('overtime-import-save', [OvertimeImportController::class, 'save']);

    Route::get('employee-portal-overtimes', [EmployeePortalOvertimeController::class, 'index']);

    //Overtime Request
    Route::get('overtime-requests', [OvertimeRequestController::class, 'index']);
    Route::get('overtime-request/{requestNumber}', [OvertimeRequestController::class, 'show']);
    Route::post('overtime-request', [OvertimeRequestController::class, 'store']);
    Route::delete('overtime-requests', [OvertimeRequestController::class, 'batchDestroy']);

    Route::get('employee-portal-overtime-requests', [EmployeePortalOvertimeRequestController::class, 'index']);
    Route::post('employee-portal-overtime-request', [EmployeePortalOvertimeRequestController::class, 'store']);
    Route::delete('employee-portal-overtime-requests', [EmployeePortalOvertimeRequestController::class, 'batchDestroy']);

    //Holiday
    Route::get('holidays', [HolidayController::class, 'index']);
    Route::get('holidays-gate', [HolidayController::class, 'indexGate']);
    Route::get('holiday-selections', [HolidayController::class, 'selection']);
    Route::post('holiday', [HolidayController::class, 'store']);
    Route::patch('holiday/{holidayUlid}', [HolidayController::class, 'update']);
    Route::delete('holidays', [HolidayController::class, 'batchDestroy']);

    //Shifts
    Route::get('shifts', [ShiftController::class, 'index']);
    Route::get('shifts-gate', [ShiftController::class, 'indexGate']);
    Route::get('shift-selections', [ShiftController::class, 'selection']);
    Route::post('shift', [ShiftController::class, 'store']);
    Route::patch('shift/{shiftId}', [ShiftController::class, 'update']);
    Route::get('shift/{ulid}', [ShiftController::class, 'show']);
    Route::get('shift-gate/{ulid}', [ShiftController::class, 'showGate']);
    Route::delete('shift/{shiftId}', [ShiftController::class, 'destroy']);
    Route::delete('shifts', [ShiftController::class, 'batchDestroy']);

    Route::get('shift-schedules-preset', [ShiftScheduleController::class, 'preset']);

    //Shift assignment
    Route::get('shift-assignments', [EmployeeShiftController::class, 'index']);
    Route::get('shift-assignments-gate', [EmployeeShiftController::class, 'indexGate']);
    Route::get('shift-assignment-selections', [EmployeeShiftController::class, 'selection']);
    Route::patch('shift-assignment/{employeeShiftId}', [EmployeeShiftController::class, 'update']);
    Route::get('shifts-by-employees', [EmployeeShiftController::class, 'shiftsByEmployees']);
    Route::post('shift-assignment-sync', [EmployeeShiftController::class, 'sync']);
    Route::post('shift-assignment-sync-without-detaching', [EmployeeShiftController::class, 'syncWithoutDetaching']);
    Route::post('shift-assignment-detach/{morphMapKey}', [EmployeeShiftController::class, 'detach']);
    Route::delete('shift-assignments', [EmployeeShiftController::class, 'batchDestroy']);

    //Leave types
    Route::get('leave-types', [LeaveTypeController::class, 'index']);
    Route::get('leave-types-gate', [LeaveTypeController::class, 'indexGate']);
    Route::get('leave-type-selections', [LeaveTypeController::class, 'selection']);
    Route::post('leave-type', [LeaveTypeController::class, 'store']);
    Route::patch('leave-type/{leaveTypeUlid}', [LeaveTypeController::class, 'update']);
    Route::get('leave-type/{ulid}', [LeaveTypeController::class, 'show']);
    Route::get('leave-type-gate/{ulid}', [LeaveTypeController::class, 'showGate']);
    Route::delete('leave-types', [LeaveTypeController::class, 'batchDestroy']);

    //Leave type assignment
    Route::get('leave-type-assignments', [EmployeeLeaveTypeController::class, 'index']);
    Route::get('leave-type-assignments-gate', [EmployeeLeaveTypeController::class, 'indexGate']);
    Route::get('leave-type-assignment-selections', [EmployeeLeaveTypeController::class, 'selection']);
    Route::patch('leave-type-assignment/{employeeLeaveTypeId}', [EmployeeLeaveTypeController::class, 'update']);
    Route::get('leave-types-by-employees', [EmployeeLeaveTypeController::class, 'leaveTypesByEmployees']);
    Route::post('leave-type-assignment-sync-without-detaching', [EmployeeLeaveTypeController::class, 'syncWithoutDetaching']);
    Route::post('leave-type-assignment-detach/{morphMapKey}', [EmployeeLeaveTypeController::class, 'detach']);
    Route::delete('leave-type-assignments', [EmployeeLeaveTypeController::class, 'batchDestroy']);

    //Leave
    Route::get('leaves', [LeaveController::class, 'index']);
    Route::get('leaves-gate', [LeaveController::class, 'indexGate']);
    Route::post('leave', [LeaveController::class, 'store']);
    Route::delete('leaves', [LeaveController::class, 'batchDestroy']);

    Route::get('employee-portal-leaves', [EmployeePortalLeaveController::class, 'index']);

    Route::get('employee-portal-leave-requests', [EmployeePortalLeaveRequestController::class, 'index']);
    Route::post('employee-portal-leave-request', [EmployeePortalLeaveRequestController::class, 'store']);
    Route::delete('employee-portal-leave-requests', [EmployeePortalLeaveRequestController::class, 'batchDestroy']);

    //Leave Request
    Route::get('leave-requests', [LeaveRequestController::class, 'index']);
    Route::get('leave-request/{requestNumber}', [LeaveRequestController::class, 'show']);
    Route::post('leave-request', [LeaveRequestController::class, 'store']);
    Route::delete('leave-requests', [LeaveRequestController::class, 'batchDestroy']);

    //Leave balance adjustment
    Route::get('leave-balance-adjustments', [LeaveBalanceAdjustmentController::class, 'index']);
    Route::get('leave-balance-adjustments-gate', [LeaveBalanceAdjustmentController::class, 'indexGate']);
    Route::post('leave-balance-adjustment', [LeaveBalanceAdjustmentController::class, 'store']);
    Route::patch('leave-balance-adjustment/{leaveBalanceAdjustmentUlid}', [LeaveBalanceAdjustmentController::class, 'update']);
    Route::delete('leave-balance-adjustments', [LeaveBalanceAdjustmentController::class, 'batchDestroy']);

    Route::get('employee-portal-leave-balance-adjustments', [EmployeePortalLeaveBalanceAdjustmentController::class, 'index']);

    //Leave balance
    Route::get('leave-running-balance-gate', [LeaveRunningBalanceController::class, 'indexGate']);
    Route::get('leave-running-balance-period-series', [LeaveRunningBalancePeriodSeriesController::class, 'index']);
    Route::post('leave-running-balance-period-series-minimum-date', [LeaveRunningBalancePeriodSeriesController::class, 'minimumDate']);
    Route::get('leave-running-balance-by-type', [LeaveRunningBalanceByTypeController::class, 'index']);

    Route::get('employee-portal-leave-running-balance-by-type', [EmployeePortalLeaveRunningBalanceByTypeController::class, 'index']);

    //Leave date range filter
    Route::post('leave-date-range-filter', [LeaveDateRangeFilterController::class, 'index']);
    //Leave date range inquire
    Route::post('leave-date-range-inquire', [LeaveDateRangeInquireController::class, 'index']);

    //Payroll
    Route::get('payroll-inquiry', [PayrollInquiryController::class, 'index']);

    Route::get('payrolls', [PayrollController::class, 'index']);
    Route::get('payroll-selections', [PayrollController::class, 'selection']);
    Route::post('payroll', [PayrollController::class, 'store']);
    Route::get('payroll/{ulid}', [PayrollController::class, 'show']);
    Route::delete('payrolls', [PayrollController::class, 'batchDestroy']);

    Route::post('pre-generate-payroll', [PreGeneratePayrollController::class, 'store']);

    //Payroll Request
    Route::get('payroll-requests', [PayrollRequestController::class, 'index']);
    Route::get('payroll-request/{requestNumber}', [PayrollRequestController::class, 'show']);
    Route::post('payroll-request', [PayrollRequestController::class, 'store']);
    Route::delete('payroll-requests', [PayrollRequestController::class, 'batchDestroy']);

    //Salary statement
    Route::get('salary-statements', [SalaryStatementController::class, 'index']);
    Route::patch('salary-statements', [SalaryStatementController::class, 'batchUpdate']);
    Route::get('salary-statements-export', [SalaryStatementController::class, 'export']);
    Route::get('salary-statement/{ulid}', [SalaryStatementController::class, 'show']);
    Route::delete('salary-statements', [SalaryStatementController::class, 'batchDestroy']);

    Route::get('employee-portal-salary-statements', [EmployeeSalaryStatementController::class, 'index']);
    Route::get('employee-portal-salary-statement/{ulid}', [EmployeeSalaryStatementController::class, 'show']);

    //Salary statement attendance
    Route::get('per-day-salary-statement-totals', [SalaryStatementAttendanceController::class, 'index']);
    Route::get('per-day-salary-statement-totals-export', [SalaryStatementAttendanceController::class, 'export']);
});

Route::group([
    'middleware' => ['auth:sanctum', 'verified'],
    'prefix' => 'v1'
], function(){

    Route::group([
        'as' => 'prototypes.'
    ], function(){
        Route::get('prototypes', [PrototypeController::class, 'index'])->name('index');
    });
});
