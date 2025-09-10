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
use App\Http\Controllers\CompensationController;
use App\Http\Controllers\CompanyFormulaController;
use App\Http\Controllers\DeductionController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\EmployeeContactController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeImportTemplateController;
use App\Http\Controllers\EmployeePayrollComponentController;
use App\Http\Controllers\EmploymentProfileController;
use App\Http\Controllers\EmploymentProfileImportTemplateController;
use App\Http\Controllers\EnumController;
use App\Http\Controllers\FormModuleController;
use App\Http\Controllers\FormulaController;
use App\Http\Controllers\Imports\EmployeeController as EmployeeImportController;
use App\Http\Controllers\Imports\EmploymentProfileController as EmploymentProfileImportController;
use App\Http\Controllers\IncomeTaxController;
use App\Http\Controllers\Internal\UtilityController;
use App\Http\Controllers\JsonController;
use App\Http\Controllers\JsonPresetController;
use App\Http\Controllers\NonEmployeeUserController;
use App\Http\Controllers\OrderableController;
use App\Http\Controllers\PayFrequencyController;
use App\Http\Controllers\TimePeriodPresetController;
use App\Http\Controllers\PrototypeController;
use App\Http\Controllers\SalaryStatementModuleController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\ShiftScheduleController;
use App\Http\Controllers\WorldController;
use App\Http\Controllers\UserCompanyAssignmentController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

if(App::environment('local', 'development')) {
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

    Route::get('model-selections/{module}', [FormModuleController::class, 'selection'])->name('selection');
    Route::get('enum-selections/{enum}', [EnumController::class, 'selection'])->name('enum.selection');
    Route::get('payroll-component-pay-selections', [EnumController::class, 'payrollComponentPaySelections']);

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

    //User
    Route::get('users', [UserController::class, 'index']);
    Route::get('user/{ulid}', [UserController::class, 'show']);
    Route::post('user-validate', [UserController::class, 'validate']);
    Route::post('user', [UserController::class, 'store']);
    Route::patch('user/{userId}', [UserController::class, 'update']);
    Route::get('user-check/{ulid}', [UserController::class, 'check']);

    Route::post('autogenerate-user-validate', [UserController::class, 'autoGenerateValidate']);
    Route::post('autogenerate-user', [UserController::class, 'autoGenerate']);

    Route::get('non-employee-user-selections', [NonEmployeeUserController::class, 'selection']);

    //Formula
    Route::get('formulas', [FormulaController::class, 'index']);
    Route::get('formula-selections', [FormulaController::class, 'selection']);
    Route::get('formula-check/{ulid}', [FormulaController::class, 'check']);
    Route::get('formula/{ulid}', [FormulaController::class, 'show']);
    Route::post('formula', [FormulaController::class, 'store']);
    Route::patch('formula/{formulaId}', [FormulaController::class, 'update']);
    Route::delete('formula/{formulaId}', [FormulaController::class, 'destroy']);

    //User-Company Assignment
    Route::get('user-company-assignment/{userUlid}', [UserCompanyAssignmentController::class, 'index']);
    Route::post('user-company-assignment/{userId}', [UserCompanyAssignmentController::class, 'sync']);

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
    Route::get('associated-company/{ulid}', [AssociatedCompanyController::class, 'show']);
    Route::patch('associated-company/{companyId}', [AssociatedCompanyController::class, 'update']);
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
    Route::post('company-formula-assignment-sync/{companyFormulaId}', [CompanyFormulaController::class, 'sync']);
    Route::post('company-formula-assignment-sync-without-detaching/{companyFormulaId}', [CompanyFormulaController::class, 'syncWithoutDetaching']);

    Route::post('read-json-file', [JsonController::class, 'read']);

    //JSON Preset
    Route::get('json-presets', [JsonPresetController::class, 'index']);
    Route::get('json-preset-check/{jsonPresetId}', [JsonPresetController::class, 'check']);
    Route::post('json-preset', [JsonPresetController::class, 'store']);
    Route::get('json-preset/{jsonPresetId}', [JsonPresetController::class, 'show']);
    Route::get('json-preset-download/{jsonPresetId}', [JsonPresetController::class, 'download']);
    Route::patch('json-preset/{jsonPresetId}', [JsonPresetController::class, 'update']);
    Route::delete('json-preset/{jsonPresetId}', [JsonPresetController::class, 'destroy']);

    //Time Period Preset Selection
    Route::get('time-period-preset-selections', [TimePeriodPresetController::class, 'selection']);

    //Pay Frequencies
    Route::get('pay-frequencies', [PayFrequencyController::class, 'index']);
    Route::get('pay-frequency-selections', [PayFrequencyController::class, 'selection']);
    Route::patch('pay-frequency/{payFrequencyId}', [PayFrequencyController::class, 'update']);

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
    Route::post('salary-statement-module', [SalaryStatementModuleController::class, 'store']);
    Route::patch('salary-statement-module/{salaryStatementModuleId}', [SalaryStatementModuleController::class, 'update']);
    Route::delete('salary-statement-module/{salaryStatementModuleId}', [SalaryStatementModuleController::class, 'destroy']);
    Route::post('re-order/salary-statement-modules', [SalaryStatementModuleController::class, 'reOrder']);

    //Employees
    Route::get('employees', [EmployeeController::class, 'index']);
    Route::post('employee-validate', [EmployeeController::class, 'validate']);
    Route::post('employee', [EmployeeController::class, 'store']);
    Route::patch('employee/{employeeId}', [EmployeeController::class, 'update']);
    Route::get('employee-selections', [EmployeeController::class, 'selection']);
    Route::get('employee/{ulid}', [EmployeeController::class, 'show']);
    Route::get('employee-check/{ulid}', [EmployeeController::class, 'check']);
    Route::post('employee-import-validate', [EmployeeImportController::class, 'read']);
    Route::post('employee-import-re-validate', [EmployeeImportController::class, 'reValidate']);
    Route::post('employee-import-save', [EmployeeImportController::class, 'save']);

    //Employee Contact
    Route::get('employee-contact/{employeeId}', [EmployeeContactController::class, 'show']);
    Route::post('employee-contact-validate', [EmployeeContactController::class, 'validate']);
    Route::post('employee-contact', [EmployeeContactController::class, 'store']);
    Route::patch('employee-contact/{employeeId}', [EmployeeContactController::class, 'update']);

    //Employment Profile
    Route::get('employment-profiles', [EmploymentProfileController::class, 'index']);
    Route::post('employment-profile-validate', [EmploymentProfileController::class, 'validate']);
    Route::post('employment-profile', [EmploymentProfileController::class, 'store']);
    Route::patch('employment-profile/{employmentProfileId}', [EmploymentProfileController::class, 'update']);
    Route::delete('employment-profile/{employmentProfileId}', [EmploymentProfileController::class, 'destroy']);
    Route::post('employment-profile-import-validate', [EmploymentProfileImportController::class, 'read']);
    Route::post('employment-profile-import-re-validate', [EmploymentProfileImportController::class, 'reValidate']);
    Route::post('employment-profile-import-save', [EmploymentProfileImportController::class, 'save']);

    //Employee Payroll Component
    Route::get('employee-payroll-info/{employeeUlid}/compensations', [EmployeePayrollComponentController::class, 'compensations']);
    Route::get('employee-payroll-info/{employeeUlid}/deductions', [EmployeePayrollComponentController::class, 'deductions']);
    Route::get('employee-payroll-info/{employeeUlid}/income-taxes', [EmployeePayrollComponentController::class, 'incomeTaxes']);

    Route::get('employee-payroll-components/{employeeUlid}', [EmployeePayrollComponentController::class, 'index']);
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

    //Shifts
    Route::get('shifts', [ShiftController::class, 'index']);
    Route::post('shift', [ShiftController::class, 'store']);
    Route::patch('shift/{shiftId}', [ShiftController::class, 'update']);
    Route::get('shift/{ulid}', [ShiftController::class, 'show']);
    Route::get('shift-check/{ulid}', [ShiftController::class, 'check']);
    Route::delete('shift/{shiftId}', [ShiftController::class, 'destroy']);

    Route::get('shift-schedules-preset', [ShiftScheduleController::class, 'preset']);
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
