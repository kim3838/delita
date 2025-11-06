<?php

namespace Database\Seeders;

use App\Blueprint\Repositories\PayFrequencyRepository;
use App\Blueprint\Repositories\SalaryStatementModuleRepository;
use App\Enums\AccountSubscriptionModules;
use App\Enums\CompanyUserAssignmentType;
use App\Enums\Compensation;
use App\Enums\Deduction;
use App\Enums\EmploymentStatus;
use App\Enums\EmploymentType;
use App\Enums\Formulable;
use App\Enums\HolidayType;
use App\Enums\IncomeTax;
use App\Enums\PayPeriod;
use App\Enums\PayType;
use App\Enums\ShiftType;
use App\Models\Account;
use App\Models\Company;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\Formula;
use App\Models\Prototype;
use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Nnjeim\World\Models\Country;

class Development extends Seeder
{
    use WithoutModelEvents;

    public array $weekdays = [];

    public function __construct()
    {
        $this->weekdays = [
            CarbonInterface::SUNDAY,
            CarbonInterface::MONDAY,
            CarbonInterface::TUESDAY,
            CarbonInterface::WEDNESDAY,
            CarbonInterface::THURSDAY,
            CarbonInterface::FRIDAY,
            CarbonInterface::SATURDAY,
        ];
    }

    public function run(): void
    {

        Prototype::factory()->count(50)->create();

        //Account 1001
        $account1001 = Account::factory()->standard()->create(['number' => 'ACCOUNT20251001', 'ulid' => Str::ulid(), 'date_registered' => Carbon::now()->toDateTimeString()]);
        //Account 1002
        $account1002 = Account::factory()->standard()->create(['number' => 'ACCOUNT20251002', 'ulid' => Str::ulid(), 'date_registered' => Carbon::now()->toDateTimeString(),]);
        //Account 1003
        $account1003 = Account::factory()->standard()->create(['number' => 'ACCOUNT20251003', 'ulid' => Str::ulid(), 'date_registered' => Carbon::now()->toDateTimeString(),]);

        $account1001->subscriptions()->create(['module' => AccountSubscriptionModules::PAYROLL, 'date_subscribed' => Carbon::now()->toDateTimeString()]);
        $account1002->subscriptions()->create(['module' => AccountSubscriptionModules::PAYROLL, 'date_subscribed' => Carbon::now()->toDateTimeString()]);
        $account1003->subscriptions()->create(['module' => AccountSubscriptionModules::PAYROLL, 'date_subscribed' => Carbon::now()->toDateTimeString()]);

        $philippines = Country::where('iso2', 'PH')->first();

        //Account 1001 Companies
        $company1001A = $account1001->companies()->create(['name' => 'Company 1001-A', 'code' => '1001-A', 'country_id' => $philippines->id, 'currency' => 'PHP', 'timezone' => 'Asia/Manila', 'ulid' => Str::ulid(),]);

        //Account 1002 Companies
        $company1002A = $account1002->companies()->create(['name' => 'Company 1002-A', 'code' => '1002-A', 'country_id' => $philippines->id, 'currency' => 'PHP', 'timezone' => 'Asia/Manila', 'ulid' => Str::ulid(),]);
        $company1002B = $account1002->companies()->create(['name' => 'Company 1002-B', 'code' => '1002-B', 'country_id' => $philippines->id, 'currency' => 'PHP', 'timezone' => 'Asia/Manila', 'ulid' => Str::ulid(),]);
        $company1002C = $account1002->companies()->create(['name' => 'Company 1002-C', 'code' => '1002-C', 'country_id' => $philippines->id, 'currency' => 'PHP', 'timezone' => 'Asia/Manila', 'ulid' => Str::ulid(),]);
        $company1002D = $account1002->companies()->create(['name' => 'Company 1002-D', 'code' => '1002-D', 'country_id' => $philippines->id, 'currency' => 'PHP', 'timezone' => 'Asia/Manila', 'ulid' => Str::ulid(),]);

        //Account 1001 Companies
        $company1003A = $account1003->companies()->create(['name' => 'Company 1003-A', 'code' => '1003-A', 'country_id' => $philippines->id, 'currency' => 'PHP', 'timezone' => 'Asia/Manila', 'ulid' => Str::ulid(),]);

        //Account 1002User01
        $account1002User01 = User::factory()->default()->create(['name' => '1002.user.1', 'email' => 'luxere20@gmail.com', 'ulid' => Str::ulid(),]);
        $account1002User02 = User::factory()->default()->create(['name' => '1002.user.2', 'email' => 'luxere20@gmail.com', 'ulid' => Str::ulid(), 'created_by' => $account1002User01->id,]);
        $account1002User03 = User::factory()->default()->create(['name' => '1002.user.3', 'email' => 'luxere20@gmail.com', 'ulid' => Str::ulid(), 'created_by' => $account1002User01->id,]);
        $account1002User04 = User::factory()->default()->create(['name' => '1002.user.4', 'email' => 'luxere20@gmail.com', 'ulid' => Str::ulid(), 'created_by' => $account1002User01->id,]);
        $user05 = User::factory()->default()->create(['name' => 'user.5', 'email' => 'luxere20@gmail.com', 'ulid' => Str::ulid(), 'created_by' => $account1002User01->id,]);

        //Company 1002-C Shifts
        //Regular no lunch out/in
        $shiftC1 = $company1002C->shifts()->create(['ulid' => Str::ulid(), 'code' => '001-DAYSHIFT-REG-2DOFF-NL0/I', 'name' => 'REGULAR 2 DAYS OFF[SUN,SAT] 09:00 AM to 05:00 PM NO LUNCH OUT/IN', 'type' => ShiftType::REGULAR, 'work_start_grace_time' => 10, 'require_lunch_time_in_and_out' => false, 'lunch_start_grace_time' => 0, 'max_overtime' => 0]);
        $this->createShiftSchedules(Shift::where('code', '001-DAYSHIFT-REG-2DOFF-NL0/I')->first(), false, ['09:00','17:00'], '08:00', true, ['12:00','13:00'], '01:00', [CarbonInterface::SUNDAY, CarbonInterface::SATURDAY]);

        //Regular no lunch out/in 4.5 hours ot
        $shiftC2 = $company1002C->shifts()->create(['ulid' => Str::ulid(), 'code' => '001-DAYSHIFT-REG-2DOFF-NL0/I-6.5MOT', 'name' => 'REGULAR 2 DAYS OFF[SUN,SAT] 09:00 AM to 05:00 PM NO LUNCH OUT/IN 06:30 MAX OT', 'type' => ShiftType::REGULAR, 'work_start_grace_time' => 10, 'require_lunch_time_in_and_out' => false, 'lunch_start_grace_time' => 0, 'max_overtime' => 6.5]);
        $this->createShiftSchedules(Shift::where('code', '001-DAYSHIFT-REG-2DOFF-NL0/I-6.5MOT')->first(), false, ['09:00','17:00'], '08:00', true, ['12:00','13:00'], '01:00', [CarbonInterface::SUNDAY, CarbonInterface::SATURDAY]);

        //Regular with lunch out/in
        $shiftC3 = $company1002C->shifts()->create(['ulid' => Str::ulid(), 'code' => '002-DAYSHIFT-REG-2DOFF-WL0/I', 'name' => 'REGULAR 2 DAYS OFF[SUN,SAT] 09:00 AM to 05:00 PM WITH LUNCH OUT/IN', 'type' => ShiftType::REGULAR, 'work_start_grace_time' => 10, 'require_lunch_time_in_and_out' => true, 'lunch_start_grace_time' => 10, 'max_overtime' => 0]);
        $this->createShiftSchedules(Shift::where('code', '002-DAYSHIFT-REG-2DOFF-WL0/I')->first(), false, ['09:00','17:00'], '08:00', true, ['12:00','13:00'], '01:00', [CarbonInterface::SUNDAY, CarbonInterface::SATURDAY]);

        //Regular with lunch out/in 4.5 hours ot
        $shiftC4 = $company1002C->shifts()->create(['ulid' => Str::ulid(), 'code' => '002-DAYSHIFT-REG-2DOFF-WL0/I-6.5MOT', 'name' => 'REGULAR 2 DAYS OFF[SUN,SAT] 09:00 AM to 05:00 PM WITH LUNCH OUT/IN 06:30 MAX OT', 'type' => ShiftType::REGULAR, 'work_start_grace_time' => 10, 'require_lunch_time_in_and_out' => true, 'lunch_start_grace_time' => 10, 'max_overtime' => 6.5]);
        $this->createShiftSchedules(Shift::where('code', '002-DAYSHIFT-REG-2DOFF-WL0/I-6.5MOT')->first(), false, ['09:00','17:00'], '08:00', true, ['12:00','13:00'], '01:00', [CarbonInterface::SUNDAY, CarbonInterface::SATURDAY]);

        //Regular flexible no lunch out/in
        $shiftC5 = $company1002C->shifts()->create(['ulid' => Str::ulid(), 'code' => '003-DAYSHIFT-FLEX-2DOFF-NL0/I', 'name' => 'FLEXIBLE 2 DAYS OFF[SUN,SAT] 00:00 AM to 00:00 AM NO LUNCH OUT/IN', 'type' => ShiftType::REGULAR, 'work_start_grace_time' => 10, 'require_lunch_time_in_and_out' => false, 'lunch_start_grace_time' => 0, 'max_overtime' => 0]);
        $this->createShiftSchedules(Shift::where('code', '003-DAYSHIFT-FLEX-2DOFF-NL0/I')->first(), true, ['00:00','00:00'], '09:00', true, ['12:00','13:00'], '01:00', [CarbonInterface::SUNDAY, CarbonInterface::SATURDAY]);

        //Regular flexible with lunch out/in
        $shiftC6 = $company1002C->shifts()->create(['ulid' => Str::ulid(), 'code' => '004-DAYSHIFT-FLEX-2DOFF-WL0/I', 'name' => 'FLEXIBLE 2 DAYS OFF[SUN,SAT] 00:00 AM to 00:00 AM WITH LUNCH OUT/IN', 'type' => ShiftType::REGULAR, 'work_start_grace_time' => 10, 'require_lunch_time_in_and_out' => true, 'lunch_start_grace_time' => 10, 'max_overtime' => 0]);
        $this->createShiftSchedules(Shift::where('code', '004-DAYSHIFT-FLEX-2DOFF-WL0/I')->first(), true, ['00:00','00:00'], '09:00', true, ['12:00','13:00'], '01:00', [CarbonInterface::SUNDAY, CarbonInterface::SATURDAY]);



        //Night midnight start no lunch out/in
        $shiftC7 = $company1002C->shifts()->create(['ulid' => Str::ulid(), 'code' => '005-GRAVEYARD-NHT-MIDNIGHT-2DOFF-NL0/I', 'name' => 'GRAVEYARD 2 DAYS OFF[SUN,SAT] 00:00 AM to 10:00 AM NO LUNCH OUT/IN', 'type' => ShiftType::GRAVEYARD, 'work_start_grace_time' => 10, 'require_lunch_time_in_and_out' => false, 'lunch_start_grace_time' => 0, 'max_overtime' => 0]);
        $this->createShiftSchedules(Shift::where('code', '005-GRAVEYARD-NHT-MIDNIGHT-2DOFF-NL0/I')->first(), false, ['00:00','10:00'], '10:00', true, ['01:00','02:00'], '01:00', [CarbonInterface::SUNDAY, CarbonInterface::SATURDAY]);

        //Night midnight start with lunch out/in
        $shiftC8 = $company1002C->shifts()->create(['ulid' => Str::ulid(), 'code' => '006-GRAVEYARD-NHT-MIDNIGHT-2DOFF-WL0/I', 'name' => 'GRAVEYARD 2 DAYS OFF[SUN,SAT] 00:00 PM to 10:00 AM WITH LUNCH OUT/IN', 'type' => ShiftType::GRAVEYARD, 'work_start_grace_time' => 10, 'require_lunch_time_in_and_out' => true, 'lunch_start_grace_time' => 10, 'max_overtime' => 0]);
        $this->createShiftSchedules(Shift::where('code', '006-GRAVEYARD-NHT-MIDNIGHT-2DOFF-WL0/I')->first(), false, ['00:00','10:00'], '10:00', true, ['01:00','02:00'], '01:00', [CarbonInterface::SUNDAY, CarbonInterface::SATURDAY]);

        //Night no lunch out/in
        $shiftC9 = $company1002C->shifts()->create(['ulid' => Str::ulid(), 'code' => '007-GRAVEYARD-NHT-2DOFF-NL0/I', 'name' => 'GRAVEYARD 2 DAYS OFF[SUN,SAT] 21:00 PM to 07:00 AM NO LUNCH OUT/IN', 'type' => ShiftType::GRAVEYARD, 'work_start_grace_time' => 10, 'require_lunch_time_in_and_out' => false, 'lunch_start_grace_time' => 0, 'max_overtime' => 0]);
        $this->createShiftSchedules(Shift::where('code', '007-GRAVEYARD-NHT-2DOFF-NL0/I')->first(), false, ['21:00','07:00'], '10:00', true, ['01:00','02:00'], '01:00', [CarbonInterface::SUNDAY, CarbonInterface::SATURDAY]);

        //Night no lunch out/in 3 hours ot
        $shiftC10 = $company1002C->shifts()->create(['ulid' => Str::ulid(), 'code' => '007-GRAVEYARD-NHT-2DOFF-NL0/I-3MOT', 'name' => 'GRAVEYARD 2 DAYS OFF[SUN,SAT] 21:00 PM to 04:00 AM NO LUNCH OUT/IN 03:00 MAX OT', 'type' => ShiftType::GRAVEYARD, 'work_start_grace_time' => 10, 'require_lunch_time_in_and_out' => false, 'lunch_start_grace_time' => 0, 'max_overtime' => 3]);
        $this->createShiftSchedules(Shift::where('code', '007-GRAVEYARD-NHT-2DOFF-NL0/I-3MOT')->first(), false, ['21:00','04:00'], '07:00', true, ['01:00','02:00'], '01:00', [CarbonInterface::SUNDAY, CarbonInterface::SATURDAY]);

        //Night no lunch out/in 10 hours ot quadruple split
        $shiftC11 = $company1002C->shifts()->create(['ulid' => Str::ulid(), 'code' => '007-GRAVEYARD-NHT-2DOFF-NL0/I-10MOTQUADSPLIT', 'name' => 'GRAVEYARD 2 DAYS OFF[SUN,SAT] 18:00 PM to 21:00 PM NO LUNCH OUT/IN 10:00 MAX OT QUADRUPLE SPLIT', 'type' => ShiftType::GRAVEYARD, 'work_start_grace_time' => 10, 'require_lunch_time_in_and_out' => false, 'lunch_start_grace_time' => 0, 'max_overtime' => 10]);
        $this->createShiftSchedules(Shift::where('code', '007-GRAVEYARD-NHT-2DOFF-NL0/I-10MOTQUADSPLIT')->first(), false, ['18:00','21:00'], '03:00', true, ['19:00','20:00'], '01:00', [CarbonInterface::SUNDAY, CarbonInterface::SATURDAY]);

        //Night with lunch out/in
        $shiftC12 = $company1002C->shifts()->create(['ulid' => Str::ulid(), 'code' => '008-GRAVEYARD-NHT-2DOFF-WL0/I', 'name' => 'GRAVEYARD 2 DAYS OFF[SUN,SAT] 21:00 PM to 07:00 AM WITH LUNCH OUT/IN', 'type' => ShiftType::GRAVEYARD, 'work_start_grace_time' => 10, 'require_lunch_time_in_and_out' => true, 'lunch_start_grace_time' => 10, 'max_overtime' => 0]);
        $this->createShiftSchedules(Shift::where('code', '008-GRAVEYARD-NHT-2DOFF-WL0/I')->first(), false, ['21:00','07:00'], '10:00', true, ['01:00','02:00'], '01:00', [CarbonInterface::SUNDAY, CarbonInterface::SATURDAY]);

        //Night with lunch out/in 3 hours ot
        $shiftC13 = $company1002C->shifts()->create(['ulid' => Str::ulid(), 'code' => '008-GRAVEYARD-NHT-2DOFF-WL0/I-3MOT', 'name' => 'GRAVEYARD 2 DAYS OFF[SUN,SAT] 21:00 PM to 04:00 AM WITH LUNCH OUT/IN 03:00 MAX OT', 'type' => ShiftType::GRAVEYARD, 'work_start_grace_time' => 10, 'require_lunch_time_in_and_out' => true, 'lunch_start_grace_time' => 10, 'max_overtime' => 3]);
        $this->createShiftSchedules(Shift::where('code', '008-GRAVEYARD-NHT-2DOFF-WL0/I-3MOT')->first(), false, ['21:00','04:00'], '07:00', true, ['01:00','02:00'], '01:00', [CarbonInterface::SUNDAY, CarbonInterface::SATURDAY]);

        //Night with lunch out/in 10 hours ot quadruple split
        $shiftC14 = $company1002C->shifts()->create(['ulid' => Str::ulid(), 'code' => '007-GRAVEYARD-NHT-2DOFF-WL0/I-10MOTQUADSPLIT', 'name' => 'GRAVEYARD 2 DAYS OFF[SUN,SAT] 18:00 PM to 21:00 PM WITH LUNCH OUT/IN 10:00 MAX OT QUADRUPLE SPLIT', 'type' => ShiftType::GRAVEYARD, 'work_start_grace_time' => 10, 'require_lunch_time_in_and_out' => true, 'lunch_start_grace_time' => 0, 'max_overtime' => 10]);
        $this->createShiftSchedules(Shift::where('code', '007-GRAVEYARD-NHT-2DOFF-WL0/I-10MOTQUADSPLIT')->first(), false, ['18:00','21:00'], '03:00', true, ['19:00','20:00'], '01:00', [CarbonInterface::SUNDAY, CarbonInterface::SATURDAY]);

        //Night no lunch out/in, lunch double split
        $shiftC15 = $company1002C->shifts()->create(['ulid' => Str::ulid(), 'code' => '009-GRAVEYARD-NHT-2DOFF-NL0/I-LUNCH-2-SPLIT', 'name' => 'GRAVEYARD 2 DAYS OFF[SUN,SAT] 21:00 PM to 07:00 AM NO LUNCH OUT/IN LUNCH DOUBLE SPLIT', 'type' => ShiftType::GRAVEYARD, 'work_start_grace_time' => 10, 'require_lunch_time_in_and_out' => false, 'lunch_start_grace_time' => 0, 'max_overtime' => 0]);
        $this->createShiftSchedules(Shift::where('code', '009-GRAVEYARD-NHT-2DOFF-NL0/I-LUNCH-2-SPLIT')->first(), false, ['21:00','07:00'], '10:00', true, ['23:30','00:30'], '01:00', [CarbonInterface::SUNDAY, CarbonInterface::SATURDAY]);
        //Night with lunch out/in, lunch double split
        $shiftC16 = $company1002C->shifts()->create(['ulid' => Str::ulid(), 'code' => '010-GRAVEYARD-NHT-2DOFF-WL0/I-LUNCH-2-SPLIT', 'name' => 'GRAVEYARD 2 DAYS OFF[SUN,SAT] 21:00 PM to 07:00 AM WITH LUNCH OUT/IN LUNCH DOUBLE SPLIT', 'type' => ShiftType::GRAVEYARD, 'work_start_grace_time' => 10, 'require_lunch_time_in_and_out' => true, 'lunch_start_grace_time' => 10, 'max_overtime' => 0]);
        $this->createShiftSchedules(Shift::where('code', '010-GRAVEYARD-NHT-2DOFF-WL0/I-LUNCH-2-SPLIT')->first(), false, ['21:00','07:00'], '10:00', true, ['23:30','00:30'], '01:00', [CarbonInterface::SUNDAY, CarbonInterface::SATURDAY]);

        //Night no lunch out/in, lunch triple split
        $shiftC17 = $company1002C->shifts()->create(['ulid' => Str::ulid(), 'code' => '011-GRAVEYARD-NHT-2DOFF-NL0/I-LUNCH-3-SPLIT', 'name' => 'GRAVEYARD 2 DAYS OFF[SUN,SAT] 21:00 PM to 07:00 AM NO LUNCH OUT/IN LUNCH TRIPLE SPLIT', 'type' => ShiftType::GRAVEYARD, 'work_start_grace_time' => 10, 'require_lunch_time_in_and_out' => false, 'lunch_start_grace_time' => 0, 'max_overtime' => 0]);
        $this->createShiftSchedules(Shift::where('code', '011-GRAVEYARD-NHT-2DOFF-NL0/I-LUNCH-3-SPLIT')->first(), false, ['21:00','07:00'], '10:00', true, ['21:30','00:30'], '01:00', [CarbonInterface::SUNDAY, CarbonInterface::SATURDAY]);
        //Night with lunch out/in, lunch triple split
        $shiftC18 = $company1002C->shifts()->create(['ulid' => Str::ulid(), 'code' => '012-GRAVEYARD-NHT-2DOFF-WL0/I-LUNCH-3-SPLIT', 'name' => 'GRAVEYARD 2 DAYS OFF[SUN,SAT] 21:00 PM to 07:00 AM WITH LUNCH OUT/IN LUNCH TRIPLE SPLIT', 'type' => ShiftType::GRAVEYARD, 'work_start_grace_time' => 10, 'require_lunch_time_in_and_out' => true, 'lunch_start_grace_time' => 10, 'max_overtime' => 0]);
        $this->createShiftSchedules(Shift::where('code', '012-GRAVEYARD-NHT-2DOFF-WL0/I-LUNCH-3-SPLIT')->first(), false, ['21:00','07:00'], '10:00', true, ['21:30','00:30'], '01:00', [CarbonInterface::SUNDAY, CarbonInterface::SATURDAY]);

        /*
         * Employee: has employee info and default assigned to a company
         * Employee Admin: has employee info and admin assigned to a company
         * Admin: no employee info and admin assigned to a company
         * */

        //Assign 1002User01 to Company 1001-A as Admin
        $account1002User01->companies()->syncWithoutDetaching([$company1001A->id => ['assignment_type' => CompanyUserAssignmentType::ADMIN]]);

        //Assign 1002User01 to Company 1002-A as Employee
        $account1002User01->companies()->syncWithoutDetaching([$company1002A->id => ['assignment_type' => CompanyUserAssignmentType::DEFAULT]]);


        //Assign 1002User01 to Company 1002-B as Admin
        $account1002User01->companies()->syncWithoutDetaching([$company1002B->id => ['assignment_type' => CompanyUserAssignmentType::ADMIN]]);

        //Assign 1002User02 to Company 1002-B as Employee
        $account1002User02->companies()->syncWithoutDetaching([$company1002B->id => ['assignment_type' => CompanyUserAssignmentType::DEFAULT]]);


        //Assign 1002User01 to Company 1002-C as Employee Admin
        $account1002User01->companies()->syncWithoutDetaching([$company1002C->id => ['assignment_type' => CompanyUserAssignmentType::ADMIN]]);

        //Assign 1002User02 to Company 1002-C as Employee
        $account1002User02->companies()->syncWithoutDetaching([$company1002C->id => ['assignment_type' => CompanyUserAssignmentType::DEFAULT]]);

        //Assign 1002User03 to Company 1002-C as Employee
        $account1002User03->companies()->syncWithoutDetaching([$company1002C->id => ['assignment_type' => CompanyUserAssignmentType::DEFAULT]]);

        //Assign 1002User04 to Company 1002-C as Admin
        $account1002User04->companies()->syncWithoutDetaching([$company1002C->id => ['assignment_type' => CompanyUserAssignmentType::ADMIN]]);

        //Assign 1002User04 to Company 1002-D as Admin
        $account1002User04->companies()->syncWithoutDetaching([$company1002D->id => ['assignment_type' => CompanyUserAssignmentType::ADMIN]]);
        /**************************************************************************************************************************************************************************************************************/

        //Company 1002-B, 1002-C Salary Statement Modules

        foreach (App::make(SalaryStatementModuleRepository::class)->defaultPresets() as $salaryStatementModule) {

            $company1002B->salaryStatementModules()->create($salaryStatementModule);
            $company1002C->salaryStatementModules()->create($salaryStatementModule);
        }

        $formulas = Formula::all();

        //Company 1002-A, 1002-B, 1002-C Assign Formula Presets
        foreach ($formulas as $formula) {

            $settings = empty($formula->default_settings?->cast) ? null : json_encode($formula->default_settings?->cast);

            $company1002A->formulas()->syncWithoutDetaching([$formula->id => ['settings' => $settings]]);
            $company1002B->formulas()->syncWithoutDetaching([$formula->id => ['settings' => $settings]]);
            $company1002C->formulas()->syncWithoutDetaching([$formula->id => ['settings' => $settings]]);
        }

        // Company 1001-A, 1002-A, 1002-B, 1002-C, and 1002-D Pay Frequencies
        foreach (App::make(PayFrequencyRepository::class)->defaultPresets() as $payFrequency) {
            $company1001A->payFrequencies()->create(['ulid' => Str::ulid(), ...$payFrequency]);
            $company1002A->payFrequencies()->create(['ulid' => Str::ulid(), ...$payFrequency]);
            $company1002B->payFrequencies()->create(['ulid' => Str::ulid(), ...$payFrequency]);
            $company1002C->payFrequencies()->create(['ulid' => Str::ulid(), ...$payFrequency]);
            $company1002D->payFrequencies()->create(['ulid' => Str::ulid(), ...$payFrequency]);
        }

        //Company 1002-B, 1002-C Pre-create Compensations
        $compensationsPresets = [
            ['code' => 'BASICSALARY', 'name' => 'Basic Salary', 'assignable' => true, 'type' => Compensation::BASIC_SALARY, 'formula' => 'Standard-Basic-Salary'],
            ['code' => 'MEAL', 'name' => 'Meal Allowance', 'assignable' => true, 'type' => Compensation::REGULAR_ALLOWANCE, 'formula' => 'Standard-Allowance'],
            ['code' => 'OVERTIME', 'name' => 'Overtime', 'assignable' => true, 'type' => Compensation::OVERTIME, 'formula' => 'Standard-Overtime'],
            ['code' => '13THMONTH', 'name' => '13th Month', 'assignable' => true, 'type' => Compensation::BENEFIT, 'formula' => 'Standard-13th-Month'],
        ];

        foreach ($compensationsPresets as $index => $compensationPreset) {
            $this->createPayrollComponent($company1002B, $index, Formulable::EARNINGS, 'compensations', $compensationPreset);
            $this->createPayrollComponent($company1002C, $index, Formulable::EARNINGS, 'compensations', $compensationPreset);
        }

        //Company 1002-B, 1002-C Pre-create Deductions
        $deductionsPresets = [
            ['code' => 'TARDINESS', 'name' => 'Tardiness', 'assignable' => true, 'type' => Deduction::DEDUCTION, 'formula' => 'Standard-Tardiness'],
            ['code' => 'UNDERTIME', 'name' => 'Undertime', 'assignable' => true, 'type' => Deduction::DEDUCTION, 'formula' => 'Standard-Undertime'],
            ['code' => 'ABSENCE', 'name' => 'Absence', 'assignable' => true, 'type' => Deduction::DEDUCTION, 'formula' => 'Standard-Absence'],
            ['code' => 'SSS', 'name' => 'SSS-Employed', 'assignable' => true, 'type' => Deduction::CONTRIBUTION, 'formula' => 'Standard-SSS-Employed-Contribution'],
            ['code' => 'PHILHEALTH', 'name' => 'Philhealth', 'assignable' => true, 'type' => Deduction::CONTRIBUTION, 'formula' => 'Standard-Philhealth-Contribution'],
            ['code' => 'PAGIBIG', 'name' => 'Pag-ibig', 'assignable' => true, 'type' => Deduction::CONTRIBUTION, 'formula' => 'Standard-Pagibig-Contribution'],
        ];

        foreach ($deductionsPresets as $index => $deductionsPreset) {
            $this->createPayrollComponent($company1002B, $index, Formulable::DEDUCTIONS, 'deductions', $deductionsPreset);
            $this->createPayrollComponent($company1002C, $index, Formulable::DEDUCTIONS, 'deductions', $deductionsPreset);
        }

        //Company 1002-B, 1002-C Pre-create Income Taxes
        $incomeTaxesPresets = [
            ['code' => 'INCOMETAX', 'name' => 'Compensation Tax', 'assignable' => true, 'type' => IncomeTax::COMPENSATION_TAX, 'formula' => 'Standard-Compensation-Tax'],
        ];

        foreach ($incomeTaxesPresets as $index => $incomeTaxesPreset) {
            $this->createPayrollComponent($company1002B, $index, Formulable::INCOME_TAX, 'incomeTaxes', $incomeTaxesPreset);
            $this->createPayrollComponent($company1002C, $index, Formulable::INCOME_TAX, 'incomeTaxes', $incomeTaxesPreset);
        }

        /**************************************************************************************************************************************************************************************************************/

        //Create Departments to Company 1002-B
        $company1002B->departments()->create(['name' => 'Executive']);
        $company1002B->departments()->create(['name' => 'HR']);
        $company1002BHrDepartment = $company1002B->departments()->where('name', 'HR')->first();
        $company1002B->departments()->create(['name' => 'Payroll', 'parent_id' => $company1002BHrDepartment->id]);
        $company1002B->departments()->create(['name' => 'Training & Development', 'parent_id' => $company1002BHrDepartment->id]);
        $company1002B->departments()->create(['name' => 'Finance & Accounting']);
        $company1002BFinanceAndAccountingDepartment = $company1002B->departments()->where('name', 'Finance & Accounting')->first();
        $company1002B->departments()->create(['name' => 'Accounts Payable', 'parent_id' => $company1002BFinanceAndAccountingDepartment->id]);
        $company1002B->departments()->create(['name' => 'Internal Audit', 'parent_id' => $company1002BFinanceAndAccountingDepartment->id]);

        //Create Departments to Company 1002-C
        $company1002C->departments()->create(['name' => 'Executive']);
        $company1002C->departments()->create(['name' => 'HR']);
        $company1002CHrDepartment = $company1002C->departments()->where('name', 'HR')->first();
        $company1002C->departments()->create(['name' => 'Payroll', 'parent_id' => $company1002CHrDepartment->id]);
        $company1002C->departments()->create(['name' => 'Training & Development', 'parent_id' => $company1002CHrDepartment->id]);
        $company1002C->departments()->create(['name' => 'Finance & Accounting']);
        $company1002CFinanceAndAccountingDepartment = $company1002C->departments()->where('name', 'Finance & Accounting')->first();
        $company1002C->departments()->create(['name' => 'Accounts Payable', 'parent_id' => $company1002CFinanceAndAccountingDepartment->id]);
        $company1002C->departments()->create(['name' => 'Internal Audit', 'parent_id' => $company1002CFinanceAndAccountingDepartment->id]);

        /**************************************************************************************************************************************************************************************************************/

        //Create Designations to Company 1002-B
        $company1002B->designations()->create(['name' => 'CEO']);
        $company1002B->designations()->create(['name' => 'HR Manager']);
        $company1002B->designations()->create(['name' => 'HR Assistant']);
        $company1002B->designations()->create(['name' => 'Account Manager']);
        $company1002B->designations()->create(['name' => 'Accounting Staff']);

        //Create Designations to Company 1002-C
        $company1002C->designations()->create(['name' => 'CEO']);
        $company1002C->designations()->create(['name' => 'HR Manager']);
        $company1002C->designations()->create(['name' => 'HR Assistant']);
        $company1002C->designations()->create(['name' => 'Account Manager']);
        $company1002C->designations()->create(['name' => 'Accounting Staff']);

        /**************************************************************************************************************************************************************************************************************/
        //Create Employee A1001 Info, Contact and Employment Profile to Company 1002-A
        $employeeA1001 = $this->createEmployee(
            $company1002A,
            $account1002User01,
            null,
            null,
            null,
            'A1001',
            'Employee 01',
            'A',
            '1002');
        $this->createEmployeeContact($employeeA1001, 'a1001.01@officemail.com', 'a1001.01@personalmail.com', '+639123456789', '+639123456789');
        $this->createEmploymentProfile($employeeA1001);
        /**************************************************************************************************************************************************************************************************************/
        //Create Employee B1001 Info, Contact and Employment Profile
        $employeeB1001 = $this->createEmployee(
            $company1002B,
            $account1002User02,
            null,
            null,
            null,
            'B1001',
            'Employee 01',
            'B',
            '1002');
        $this->createEmployeeContact($employeeB1001, 'b1001.01@officemail.com');
        $this->createEmploymentProfile($employeeB1001);
        /**************************************************************************************************************************************************************************************************************/
        //Create Employee C1001 Info, Contact, Employment Profile and Shift
        $employeeC1001 = $this->createEmployee(
            $company1002C,
            $account1002User01,
            $company1002C->departments()->where('name', 'HR')->first(),
            $company1002C->designations()->where('name', 'HR Manager')->first(),
            null,
            'C1001', 'Employee 01', 'C', '1002');
        $this->createEmployeeContact($employeeC1001, 'c1001.01@officemail.com', 'c1001.01@personalmail.com');
        $this->createEmploymentProfile($employeeC1001);
        $employeeC1001->shifts()->syncWithoutDetaching([$shiftC1->id => ['start_date' => '2025-01-10', 'stated_shift_end_date' => false,]]);
        /**************************************************************************************************************************************************************************************************************/
        //Create Employee C1002 Info, Contact, Employment Profile and Shift
        $employeeC1002 = $this->createEmployee(
            $company1002C,
            $account1002User02,
            $company1002C->departments()->where('name', 'Accounts Payable')->first(),
            $company1002C->designations()->where('name', 'Accounting Staff')->first(),
            null,
            'C1002', 'Employee 02', 'C', '1002');
        $this->createEmployeeContact($employeeC1002, 'c1002.01@officemail.com', 'c1002.01@personalmail.com', '+639122256789');
        $this->createEmploymentProfile($employeeC1002);
        $employeeC1002->shifts()->syncWithoutDetaching([$shiftC2->id => ['start_date' => '2025-01-10', 'stated_shift_end_date' => false,]]);
        /**************************************************************************************************************************************************************************************************************/
        //Create Employee C1003 Info, Contact, Employment Profile and Shift
        $employeeC1003 = $this->createEmployee(
            $company1002C,
            $account1002User03,
            $company1002C->departments()->where('name', 'Accounts Payable')->first(),
            $company1002C->designations()->where('name', 'Accounting Staff')->first(),
            $employeeC1001,
            'C1003', 'Employee 03', 'C', '1002');
        $this->createEmployeeContact($employeeC1003, 'c1003.01@officemail.com', '', '', '+639122111789');
        $this->createEmploymentProfile($employeeC1003);
        $employeeC1003->shifts()->syncWithoutDetaching([$shiftC3->id => ['start_date' => '2025-01-10', 'stated_shift_end_date' => false,]]);

        //Create Employee C1004 to C1018 Info, Contact, Employment Profile and Shift
        for ($i = 4; $i <= 18; $i++) {

            ${"employee$i"} = $this->createEmployee(
                $company1002C, null, null,null,null,
                "C10" . sprintf('%02d', $i), "Employee " . sprintf('%02d', $i), 'C', '1002');
            $this->createEmployeeContact(${"employee$i"}, "c100$i.01@officemail.com");
            $this->createEmploymentProfile(${"employee$i"});
            ${"employee$i"}->shifts()->syncWithoutDetaching([${"shiftC$i"}->id => ['start_date' => '2025-01-10', 'stated_shift_end_date' => false,]]);
        }
        /**************************************************************************************************************************************************************************************************************/

        //Company 1002-B Monthly Pay Frequency
        $company1002BMonthlyPayFrequency = $company1002B->payFrequencies()->where('code', 'MONTHLY')->first();

        //Company 1002-B Compensations
        $company1002BBasicSalary = $company1002B->compensations->where('name', 'Basic Salary')->where('type', Compensation::BASIC_SALARY)->first();
        $company1002BMealAllowance = $company1002B->compensations->where('name', 'Meal Allowance')->where('type', Compensation::REGULAR_ALLOWANCE)->first();
        $company1002BOvertime = $company1002B->compensations->where('name', 'Overtime')->where('type', Compensation::OVERTIME)->first();

        //Company 1002-B Deductions
        $company1002BTardiness = $company1002B->deductions->where('name', 'Tardiness')->where('type', Deduction::DEDUCTION)->first();
        $company1002BUndertime = $company1002B->deductions->where('name', 'Undertime')->where('type', Deduction::DEDUCTION)->first();
        $company1002BAbsent = $company1002B->deductions->where('name', 'Absence')->where('type', Deduction::DEDUCTION)->first();
        $company1002BSSSEmployed = $company1002B->deductions->where('name', 'SSS-Employed')->where('type', Deduction::CONTRIBUTION)->first();

        //Company 1002-B Income Taxes
        $company1002BCompensationTax = $company1002B->incomeTaxes->where('name', 'Compensation Tax')->where('type', IncomeTax::COMPENSATION_TAX)->first();

        //Create Compensations for Employee B1001
        $employeeB1001->payrollComponents()->create(['formulable_type' => Formulable::EARNINGS , 'payroll_componentable_id' => $company1002BBasicSalary->id, 'payroll_componentable_type' => 'compensation', 'amount' => '1200.14','currency' => 'PHP', 'pay_period' => PayPeriod::MONTHLY, 'pay_type' => PayType::BY_ATTENDANCE, 'pay_frequency_id' => $company1002BMonthlyPayFrequency->id]);
        $employeeB1001->payrollComponents()->create(['formulable_type' => Formulable::EARNINGS , 'payroll_componentable_id' => $company1002BMealAllowance->id, 'payroll_componentable_type' => 'compensation', 'amount' => '200', 'currency' => 'PHP', 'pay_period' => PayPeriod::MONTHLY, 'pay_type' => PayType::BY_ATTENDANCE, 'pay_frequency_id' => $company1002BMonthlyPayFrequency->id]);
        $employeeB1001->payrollComponents()->create(['formulable_type' => Formulable::EARNINGS , 'payroll_componentable_id' => $company1002BOvertime->id, 'payroll_componentable_type' => 'compensation']);
        //Create Deductions for Employee B1001
        $employeeB1001->payrollComponents()->create(['formulable_type' => Formulable::DEDUCTIONS , 'payroll_componentable_id' => $company1002BTardiness->id, 'payroll_componentable_type' => 'deduction']);
        $employeeB1001->payrollComponents()->create(['formulable_type' => Formulable::DEDUCTIONS , 'payroll_componentable_id' => $company1002BUndertime->id, 'payroll_componentable_type' => 'deduction']);
        $employeeB1001->payrollComponents()->create(['formulable_type' => Formulable::DEDUCTIONS , 'payroll_componentable_id' => $company1002BAbsent->id, 'payroll_componentable_type' => 'deduction']);
        $employeeB1001->payrollComponents()->create(['formulable_type' => Formulable::DEDUCTIONS , 'payroll_componentable_id' => $company1002BSSSEmployed->id, 'payroll_componentable_type' => 'deduction']);
        //Create Income Tax for Employee B1001
        $employeeB1001->payrollComponents()->create(['formulable_type' => Formulable::INCOME_TAX , 'payroll_componentable_id' => $company1002BCompensationTax->id, 'payroll_componentable_type' => 'income_tax']);

        //Company 1002-C Monthly Pay Frequency
        $company1002CMonthlyPayFrequency = $company1002C->payFrequencies()->where('code', 'MONTHLY')->first();

        //Company 1002-C Compensations
        $company1002CBasicSalary = $company1002C->compensations->where('name', 'Basic Salary')->where('type', Compensation::BASIC_SALARY)->first();
        $company1002CMealAllowance = $company1002C->compensations->where('name', 'Meal Allowance')->where('type', Compensation::REGULAR_ALLOWANCE)->first();
        $company1002COvertime = $company1002C->compensations->where('name', 'Overtime')->where('type', Compensation::OVERTIME)->first();

        //Company 1002-C Deductions
        $company1002CTardiness = $company1002C->deductions->where('name', 'Tardiness')->where('type', Deduction::DEDUCTION)->first();
        $company1002CUndertime = $company1002B->deductions->where('name', 'Undertime')->where('type', Deduction::DEDUCTION)->first();
        $company1002CAbsent = $company1002C->deductions->where('name', 'Absence')->where('type', Deduction::DEDUCTION)->first();
        $company1002CSSSEmployed = $company1002C->deductions->where('name', 'SSS-Employed')->where('type', Deduction::CONTRIBUTION)->first();

        //Company 1002-C Income Taxes
        $company1002CCompensationTax = $company1002C->incomeTaxes->where('name', 'Compensation Tax')->where('type', IncomeTax::COMPENSATION_TAX)->first();

        //Create Compensations for Employee C1001
        $employeeC1001->payrollComponents()->create(['formulable_type' => Formulable::EARNINGS , 'payroll_componentable_id' => $company1002CBasicSalary->id, 'payroll_componentable_type' => 'compensation', 'amount' => '1200.14', 'currency' => 'PHP', 'pay_period' => PayPeriod::MONTHLY, 'pay_type' => PayType::BY_ATTENDANCE, 'pay_frequency_id' => $company1002CMonthlyPayFrequency->id]);
        $employeeC1001->payrollComponents()->create(['formulable_type' => Formulable::EARNINGS , 'payroll_componentable_id' => $company1002CMealAllowance->id, 'payroll_componentable_type' => 'compensation', 'amount' => '200', 'currency' => 'PHP', 'pay_period' => PayPeriod::MONTHLY, 'pay_type' => PayType::BY_ATTENDANCE, 'pay_frequency_id' => $company1002CMonthlyPayFrequency->id]);
        $employeeC1001->payrollComponents()->create(['formulable_type' => Formulable::EARNINGS , 'payroll_componentable_id' => $company1002COvertime->id, 'payroll_componentable_type' => 'compensation']);
        //Create Deductions for Employee C1001
        $employeeC1001->payrollComponents()->create(['formulable_type' => Formulable::DEDUCTIONS , 'payroll_componentable_id' => $company1002CTardiness->id, 'payroll_componentable_type' => 'deduction']);
        $employeeC1001->payrollComponents()->create(['formulable_type' => Formulable::DEDUCTIONS , 'payroll_componentable_id' => $company1002CUndertime->id, 'payroll_componentable_type' => 'deduction']);
        $employeeC1001->payrollComponents()->create(['formulable_type' => Formulable::DEDUCTIONS , 'payroll_componentable_id' => $company1002CAbsent->id, 'payroll_componentable_type' => 'deduction']);
        $employeeC1001->payrollComponents()->create(['formulable_type' => Formulable::DEDUCTIONS , 'payroll_componentable_id' => $company1002CSSSEmployed->id, 'payroll_componentable_type' => 'deduction']);
        //Create Income Tax for Employee C1001
        $employeeC1001->payrollComponents()->create(['formulable_type' => Formulable::INCOME_TAX , 'payroll_componentable_id' => $company1002CCompensationTax->id, 'payroll_componentable_type' => 'income_tax']);

        //Create Compensations for Employee C1002
        $employeeC1002->payrollComponents()->create(['formulable_type' => Formulable::EARNINGS , 'payroll_componentable_id' => $company1002CBasicSalary->id, 'payroll_componentable_type' => 'compensation', 'amount' => '100', 'currency' => 'PHP', 'pay_period' => PayPeriod::MONTHLY, 'pay_type' => PayType::BY_ATTENDANCE, 'pay_frequency_id' => $company1002CMonthlyPayFrequency->id]);
        $employeeC1002->payrollComponents()->create(['formulable_type' => Formulable::EARNINGS , 'payroll_componentable_id' => $company1002CMealAllowance->id, 'payroll_componentable_type' => 'compensation', 'amount' => '10', 'currency' => 'PHP', 'pay_period' => PayPeriod::MONTHLY, 'pay_type' => PayType::BY_ATTENDANCE, 'pay_frequency_id' => $company1002CMonthlyPayFrequency->id]);
        $employeeC1002->payrollComponents()->create(['formulable_type' => Formulable::EARNINGS , 'payroll_componentable_id' => $company1002COvertime->id, 'payroll_componentable_type' => 'compensation']);
        //Create Deductions for Employee C1002
        $employeeC1002->payrollComponents()->create(['formulable_type' => Formulable::DEDUCTIONS , 'payroll_componentable_id' => $company1002CTardiness->id, 'payroll_componentable_type' => 'deduction']);
        $employeeC1002->payrollComponents()->create(['formulable_type' => Formulable::DEDUCTIONS , 'payroll_componentable_id' => $company1002CUndertime->id, 'payroll_componentable_type' => 'deduction']);
        $employeeC1002->payrollComponents()->create(['formulable_type' => Formulable::DEDUCTIONS , 'payroll_componentable_id' => $company1002CAbsent->id, 'payroll_componentable_type' => 'deduction']);
        $employeeC1002->payrollComponents()->create(['formulable_type' => Formulable::DEDUCTIONS , 'payroll_componentable_id' => $company1002CSSSEmployed->id, 'payroll_componentable_type' => 'deduction']);
        //Create Income Tax for Employee C1002
        $employeeC1002->payrollComponents()->create(['formulable_type' => Formulable::INCOME_TAX , 'payroll_componentable_id' => $company1002CCompensationTax->id, 'payroll_componentable_type' => 'income_tax']);

        //Create Compensations for Employee C1003
        $employeeC1003->payrollComponents()->create(['formulable_type' => Formulable::EARNINGS , 'payroll_componentable_id' => $company1002CBasicSalary->id, 'payroll_componentable_type' => 'compensation', 'amount' => '420', 'currency' => 'PHP', 'pay_period' => PayPeriod::MONTHLY, 'pay_type' => PayType::BY_ATTENDANCE, 'pay_frequency_id' => $company1002CMonthlyPayFrequency->id]);
        $employeeC1003->payrollComponents()->create(['formulable_type' => Formulable::EARNINGS , 'payroll_componentable_id' => $company1002CMealAllowance->id, 'payroll_componentable_type' => 'compensation', 'amount' => '20', 'currency' => 'PHP', 'pay_period' => PayPeriod::MONTHLY, 'pay_type' => PayType::BY_ATTENDANCE, 'pay_frequency_id' => $company1002CMonthlyPayFrequency->id]);
        $employeeC1003->payrollComponents()->create(['formulable_type' => Formulable::EARNINGS , 'payroll_componentable_id' => $company1002COvertime->id, 'payroll_componentable_type' => 'compensation']);
        //Create Deductions for Employee C1003
        $employeeC1003->payrollComponents()->create(['formulable_type' => Formulable::DEDUCTIONS , 'payroll_componentable_id' => $company1002CTardiness->id, 'payroll_componentable_type' => 'deduction']);
        $employeeC1003->payrollComponents()->create(['formulable_type' => Formulable::DEDUCTIONS , 'payroll_componentable_id' => $company1002CUndertime->id, 'payroll_componentable_type' => 'deduction']);
        $employeeC1003->payrollComponents()->create(['formulable_type' => Formulable::DEDUCTIONS , 'payroll_componentable_id' => $company1002CAbsent->id, 'payroll_componentable_type' => 'deduction']);
        $employeeC1003->payrollComponents()->create(['formulable_type' => Formulable::DEDUCTIONS , 'payroll_componentable_id' => $company1002CSSSEmployed->id, 'payroll_componentable_type' => 'deduction']);
        //Create Income Tax for Employee C1003
        $employeeC1003->payrollComponents()->create(['formulable_type' => Formulable::INCOME_TAX , 'payroll_componentable_id' => $company1002CCompensationTax->id, 'payroll_componentable_type' => 'income_tax']);

        /**************************************************************************************************************************************************************************************************************/

        //Company 1002-C Holidays
        $company1002C->holidays()->create([
            'ulid' => Str::ulid(),
            'name' => 'Legal Recurring Jan 11th',
            'type' => HolidayType::LEGAL,
            'date' => '2000-01-11',
            'recurring' => true,
            'active' => true,
            'effective_date' => '2000-01-11',
        ]);
        $company1002C->holidays()->create([
            'ulid' => Str::ulid(),
            'name' => 'Special Recurring Jan 13th',
            'type' => HolidayType::SPECIAL,
            'date' => '2000-01-13',
            'recurring' => true,
            'active' => true,
            'effective_date' => '2000-01-11',
        ]);
    }

    public function createPayrollComponent(Model $company, $index, $formulableType, $component, $attributes): void
    {
        $formulas = $company->formulas;

        $company->{$component}()->create([
            ...collect($attributes)->except('formula')->toArray(),
            'order' => ++$index,
            'company_formula_id' => $formulas->where('formulable_type', $formulableType)
                ->where('component_type', $attributes['type'])
                ->where('name', $attributes['formula'])
                ->first()->pivot->id,
        ]);
    }

    public function createShiftSchedules(Shift $shift, $flexible = false, $workSchedule = ['09:00','17:00'], $totalWorkHoursWithBreaks = '08:00', $hasLunch = false, $lunchSchedule = ['12:00','13:00'], $totalLunch = '01:00', $dayoffs = []): void
    {
        $restDays = [
            CarbonInterface::SUNDAY,
            CarbonInterface::SATURDAY,
        ];

        foreach ($this->weekdays as $weekday) {

            $dayOff = in_array($weekday, $dayoffs);

            if($dayOff){

                $shift->schedules()->create([
                    'week_day' => $weekday,
                    'is_rest_day' => in_array($weekday, $restDays),
                    'is_day_off' => true,
                    'timezone' => null,
                    'is_flexible' => $flexible,
                    'work_start' => null,
                    'work_end' => null,
                    'total_work_hours_with_breaks' => null,
                    'has_lunch_break' => false,
                    'lunch_break_start' => null,
                    'lunch_break_end' => null,
                    'total_lunch_break_hours' => null
                ]);

            } else {

                $shift->schedules()->create([
                    'week_day' => $weekday,
                    'is_rest_day' => in_array($weekday, $restDays),
                    'is_day_off' => false,
                    'timezone' => 'Asia/Manila',
                    'is_flexible' => $flexible,
                    'work_start' => $workSchedule[0],
                    'work_end' => $workSchedule[1],
                    'total_work_hours_with_breaks' => $totalWorkHoursWithBreaks,
                    'has_lunch_break' => $hasLunch,
                    'lunch_break_start' => $lunchSchedule[0],
                    'lunch_break_end' => $lunchSchedule[1],
                    'total_lunch_break_hours' => $totalLunch
                ]);
            }
        }
    }

    public function createEmployee(
        Company $company,
        ?User $user = null,
        ?Department $department = null,
        ?Designation $designation = null,
        ?Employee $manager = null,
        $number = null,
        $givenName = null,
        $middleName = null,
        $familyName = null,
    ){
        $baseModel = empty($user) ? $company : $user;

        return $baseModel->employees()->create([
            'ulid' => Str::ulid(),
            'company_id' => $company->id,
            'department_id' => $department?->id,
            'designation_id' => $designation?->id,
            'manager_id' => $manager?->id,
            'number' => $number,
            'given_name' => $givenName,
            'middle_name' => $middleName,
            'family_name' => $familyName,
        ]);
    }

    public function createEmployeeContact(Employee $employee, $officeEmail = '', $personalEmail = '', $officePhone = '', $personalPhone = '')
    {
        return $employee->contact()->create([
            'office_email' => $officeEmail,
            'personal_email' => $personalEmail,
            'office_phone' => $officePhone,
            'personal_phone' => $personalPhone
        ]);
    }

    public function createEmploymentProfile(Employee $employee, EmploymentType $employmentType = EmploymentType::NOT_SPECIFIED)
    {
        return $employee->employmentProfiles()->create([
            'status' => EmploymentStatus::ACTIVE,
            'employment_type' => $employmentType,
            'start_date' => Carbon::now()->toDateString()
        ]);
    }
}
