<?php

namespace App\Providers;

use App\Models\Account;
use App\Models\Attendance;
use App\Models\AttendanceDetail;
use App\Models\Company;
use App\Models\Compensation;
use App\Models\Deduction;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\Formula;
use App\Models\Group;
use App\Models\Holiday;
use App\Models\IncomeTax;
use App\Models\JsonPreset;
use App\Models\Overtime;
use App\Models\PayFrequency;
use App\Models\SalaryStatementModule;
use App\Models\Shift;
use App\Models\ShiftSchedule;
use App\Models\TimePeriodPreset;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Relation::enforceMorphMap([
            'user' => User::class,
            'account' => Account::class,
            'formula' => Formula::class,
            'json_preset' => JsonPreset::class,
            'company' => Company::class,
            'pay_frequency' => PayFrequency::class,
            'employee' => Employee::class,
            'department' => Department::class,
            'group' => Group::class,
            'designation' => Designation::class,
            'compensation' => Compensation::class,
            'deduction' => Deduction::class,
            'income_tax' => IncomeTax::class,
            'time_period_preset' => TimePeriodPreset::class,
            'salary_statement_module' => SalaryStatementModule::class,
            'shift' => Shift::class,
            'shift_schedule' => ShiftSchedule::class,
            'attendance' => Attendance::class,
            'attendance_detail' => AttendanceDetail::class,
            'overtime' => Overtime::class,
            'holiday' => Holiday::class,
        ]);
    }
}
