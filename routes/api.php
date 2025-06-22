<?php

use App\Http\Controllers\AssociatedCompanyController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmedTwoFactorAuthenticationController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\TwoFactorAuthenticatedSessionController;
use App\Http\Controllers\Auth\TwoFactorAuthenticationController;
use App\Http\Controllers\Auth\TwoFactorQrCodeController;
use App\Http\Controllers\Auth\TwoFactorRecoveryCodeController;
use App\Http\Controllers\Auth\TwoFactorSecretKeyController;
use App\Http\Controllers\Auth\UpdateUserPasswordController;
use App\Http\Controllers\CompanyPayPeriodSettingController;
use App\Http\Controllers\CompensationController;
use App\Http\Controllers\CompanyFormulaController;
use App\Http\Controllers\DeductionController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EnumController;
use App\Http\Controllers\FormModuleController;
use App\Http\Controllers\IncomeTaxController;
use App\Http\Controllers\Internal\UtilityController;
use App\Http\Controllers\OrderableController;
use App\Http\Controllers\PayPeriodPresetController;
use App\Http\Controllers\PayPeriodSettingController;
use App\Http\Controllers\PrototypeController;
use App\Http\Controllers\SalaryStatementModuleController;
use Illuminate\Support\Facades\Route;

Route::get('model-selections/{module}', [FormModuleController::class, 'selection'])->name('selection');
Route::get('enum-selections/{enum}', [EnumController::class, 'selection'])->name('enum.selection');

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

    //User relation
    Route::get('associated-companies', [AssociatedCompanyController::class, 'index']);

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

    //Pay Period
    Route::patch('pay-period-setting/{payPeriodSettingId}', [PayPeriodSettingController::class, 'update']);

    //Compensation
    Route::get('compensations', [CompensationController::class, 'index']);
    Route::post('compensation', [CompensationController::class, 'store']);
    Route::patch('compensation/{compensationId}', [CompensationController::class, 'update']);
    Route::delete('compensation/{compensationId}', [CompensationController::class, 'destroy']);

    //Deduction
    Route::get('deductions', [DeductionController::class, 'index']);
    Route::post('deduction', [DeductionController::class, 'store']);
    Route::patch('deduction/{deductionId}', [DeductionController::class, 'update']);
    Route::delete('deduction/{deductionId}', [DeductionController::class, 'destroy']);

    //Income Tax
    Route::get('income-taxes', [IncomeTaxController::class, 'index']);
    Route::post('income-tax', [IncomeTaxController::class, 'store']);
    Route::patch('income-tax/{incomeTaxId}', [IncomeTaxController::class, 'update']);
    Route::delete('income-tax/{incomeTaxId}', [IncomeTaxController::class, 'destroy']);

    Route::get('salary-statement-modules', [SalaryStatementModuleController::class, 'index']);
    Route::post('re-order/salary-statement-modules', [SalaryStatementModuleController::class, 'reOrder']);

    //Employees
    Route::get('employees', [EmployeeController::class, 'index']);
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
