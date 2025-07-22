<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AssociatedAccountController;
use App\Http\Controllers\AssociatedCompanyController;
use App\Http\Controllers\AssociatedUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmedTwoFactorAuthenticationController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\TwoFactorAuthenticatedSessionController;
use App\Http\Controllers\Auth\TwoFactorAuthenticationController;
use App\Http\Controllers\Auth\TwoFactorQrCodeController;
use App\Http\Controllers\Auth\TwoFactorRecoveryCodeController;
use App\Http\Controllers\Auth\TwoFactorSecretKeyController;
use App\Http\Controllers\Auth\UpdateUserPasswordController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CompanyPayPeriodSettingController;
use App\Http\Controllers\CompensationController;
use App\Http\Controllers\CompanyFormulaController;
use App\Http\Controllers\DeductionController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeePayrollComponentController;
use App\Http\Controllers\EnumController;
use App\Http\Controllers\FormModuleController;
use App\Http\Controllers\IncomeTaxController;
use App\Http\Controllers\Internal\UtilityController;
use App\Http\Controllers\OrderableController;
use App\Http\Controllers\PayPeriodPresetController;
use App\Http\Controllers\PayPeriodSettingController;
use App\Http\Controllers\PrototypeController;
use App\Http\Controllers\SalaryStatementModuleController;
use App\Http\Controllers\TimezoneController;
use Illuminate\Support\Facades\Route;

Route::get('model-selections/{module}', [FormModuleController::class, 'selection'])->name('selection');
Route::get('enum-selections/{enum}', [EnumController::class, 'selection'])->name('enum.selection');
Route::get('timezone-selections', [TimezoneController::class, 'selection']);

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
    Route::post('debug', [UtilityController::class, 'debug']);

    //Verify X-XSRF-TOKEN on destructive action
    Route::post('post', [UtilityController::class, 'post']);
});

Route::group([
    'middleware' => ['auth:sanctum']
], function(){

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
    Route::get('account-check/{ulid}', [AccountController::class, 'check']);
    Route::post('account', [AccountController::class, 'store']);
    Route::patch('account/{accountId}', [AccountController::class, 'update']);

    //Company
    Route::get('companies', [CompanyController::class, 'index']);
    Route::get('company-selections', [CompanyController::class, 'selection']);
    Route::get('company/{ulid}', [CompanyController::class, 'show']);
    Route::get('company-check/{ulid}', [CompanyController::class, 'check']);
    Route::post('company', [CompanyController::class, 'store']);
    Route::patch('company/{companyId}', [CompanyController::class, 'update']);

    //User relation
    Route::get('is-admin-in-any-company', [AuthenticatedSessionController::class, 'isAdminInAnyCompany']);

    Route::get('associated-users', [AssociatedUserController::class, 'index']);

    Route::get('associated-accounts', [AssociatedAccountController::class, 'index']);
    Route::get('associated-account-selections', [AssociatedAccountController::class, 'selection']);

    Route::get('associated-companies', [AssociatedCompanyController::class, 'index']);
    Route::get('associated-company-selections', [AssociatedCompanyController::class, 'selection']);

    //Email verification
    Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])->name('verification.send');

    //Two-factor authentication
    Route::post('two-factor-authentication', [TwoFactorAuthenticationController::class, 'store'])->name('two-factor.enable');
    Route::get('two-factor-qr-code', [TwoFactorQrCodeController::class, 'show'])->name('two-factor.qr-code');
    Route::get('two-factor-secret-key', [TwoFactorSecretKeyController::class, 'show'])->name('two-factor.secret-key');
    Route::get('two-factor-recovery-codes', [TwoFactorRecoveryCodeController::class, 'index'])->name('two-factor.recovery-codes');
    Route::post('confirmed-two-factor-authentication', [ConfirmedTwoFactorAuthenticationController::class, 'store'])->name('two-factor.confirm');
    Route::delete('two-factor-authentication', [TwoFactorAuthenticationController::class, 'destroy']);

    //Common
    Route::post('orderable/re-order/{module}', [OrderableController::class, 'reOrder']);

    //Company Formula
    Route::get('company-formula-selections', [CompanyFormulaController::class, 'selection']);
    Route::get('company-formula/{companyFormulaId}', [CompanyFormulaController::class, 'show']);
    Route::get('company-formulas', [CompanyFormulaController::class, 'index']);

    //Pay Period Preset Selection
    Route::get('pay-period-preset-selections', [PayPeriodPresetController::class, 'selection']);

    //Company Pay Period Setting
    Route::get('company-pay-period-setting/{companyId}', [CompanyPayPeriodSettingController::class, 'index']);
    Route::post('pay-period-setting', [PayPeriodSettingController::class, 'store']);
    Route::patch('pay-period-setting/{payPeriodSettingId}', [PayPeriodSettingController::class, 'update']);

    //Compensation
    Route::get('compensations', [CompensationController::class, 'index']);
    Route::get('compensation-selections', [CompensationController::class, 'selection']);
    Route::post('compensation', [CompensationController::class, 'store']);
    Route::patch('compensation/{compensationId}', [CompensationController::class, 'update']);
    Route::delete('compensation/{compensationId}', [CompensationController::class, 'destroy']);

    //Deduction
    Route::get('deductions', [DeductionController::class, 'index']);
    Route::get('deduction-selections', [DeductionController::class, 'selection']);
    Route::post('deduction', [DeductionController::class, 'store']);
    Route::patch('deduction/{deductionId}', [DeductionController::class, 'update']);
    Route::delete('deduction/{deductionId}', [DeductionController::class, 'destroy']);

    //Income Tax
    Route::get('income-taxes', [IncomeTaxController::class, 'index']);
    Route::get('income-tax-selections', [IncomeTaxController::class, 'selection']);
    Route::post('income-tax', [IncomeTaxController::class, 'store']);
    Route::patch('income-tax/{incomeTaxId}', [IncomeTaxController::class, 'update']);
    Route::delete('income-tax/{incomeTaxId}', [IncomeTaxController::class, 'destroy']);

    //Salary Statement Modules
    Route::get('salary-statement-modules', [SalaryStatementModuleController::class, 'index']);
    Route::post('re-order/salary-statement-modules', [SalaryStatementModuleController::class, 'reOrder']);

    //Employees
    Route::get('employees', [EmployeeController::class, 'index']);
    Route::get('employee-selections', [EmployeeController::class, 'selection']);
    Route::get('employee/{ulid}', [EmployeeController::class, 'show']);
    Route::get('employee-check/{ulid}', [EmployeeController::class, 'check']);

    //Employee Payroll Component
    Route::get('employee-payroll-info/{employeeUlid}/compensations', [EmployeePayrollComponentController::class, 'compensations']);
    Route::get('employee-payroll-info/{employeeUlid}/deductions', [EmployeePayrollComponentController::class, 'deductions']);
    Route::get('employee-payroll-info/{employeeUlid}/income-taxes', [EmployeePayrollComponentController::class, 'incomeTaxes']);

    Route::post('employee-payroll-component-validate', [EmployeePayrollComponentController::class, 'validate']);
    Route::post('employee-payroll-component', [EmployeePayrollComponentController::class, 'store']);
    Route::patch('employee-payroll-component/{employeePayrollComponentId}', [EmployeePayrollComponentController::class, 'update']);
    Route::delete('employee-payroll-component/{employeePayrollComponentId}', [EmployeePayrollComponentController::class, 'destroy']);

    //Designations
    Route::get('designations', [DesignationController::class, 'index']);
    Route::get('designation-selections', [DesignationController::class, 'selection']);
    Route::post('designation', [DesignationController::class, 'store']);
    Route::patch('designation/{designationId}', [DesignationController::class, 'update']);
    Route::delete('designation/{designationId}', [DesignationController::class, 'destroy']);

    //Departments
    Route::get('departments', [DepartmentController::class, 'index']);
    Route::get('department-selections', [DepartmentController::class, 'selection']);
    Route::post('department', [DepartmentController::class, 'store']);
    Route::patch('department/{departmentId}', [DepartmentController::class, 'update']);
    Route::delete('department/{departmentId}', [DepartmentController::class, 'destroy']);
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
