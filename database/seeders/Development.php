<?php

namespace Database\Seeders;

use App\Blueprint\Repositories\PayFrequencyRepository;
use App\Blueprint\Repositories\SalaryStatementModuleRepository;
use App\Concrete\ApprovalService;
use App\Enums\AccountSubscriptionPlan;
use App\Enums\AccountSubscriptionModule;
use App\Enums\CompanyUserAssignmentType;
use App\Enums\Compensation;
use App\Enums\Deduction;
use App\Enums\DepartmentEmployeeAssignmentType;
use App\Enums\EmploymentStatus;
use App\Enums\EmploymentType;
use App\Enums\EndOfServiceType;
use App\Enums\Formulable;
use App\Enums\HolidayType;
use App\Enums\IncomeTax;
use App\Enums\LeaveBalanceAdjustmentType;
use App\Enums\LeaveCarryOverType;
use App\Enums\LeaveIntervalSpanType;
use App\Enums\LeavePeriodType;
use App\Enums\LeaveType as LeaveTypeEnum;
use App\Enums\LeaveUsageSpanType;
use App\Enums\PayPeriod;
use App\Enums\PayType;
use App\Enums\ShiftHolidayPolicy;
use App\Enums\ShiftType;
use App\Events\Repositories\AccountCreated;
use App\Listeners\AccountCreatedChain;
use App\Models\Account;
use App\Models\Company;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\Formula;
use App\Models\PayFrequency;
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
use App\Models\Country;

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
        $account1001 = Account::query()->firstOrCreate(
            ['number' => 'ACCOUNT20251001'],
            ['number' => 'ACCOUNT20251001', 'ulid' => Str::ulid(), 'email' => 'luxere20@gmail.com', 'date_registered' => Carbon::now()->toDateTimeString()]
        );

        //Account 1002
        $account1002 = Account::query()->firstOrCreate(
            ['number' => 'ACCOUNT20251002'],
            ['number' => 'ACCOUNT20251002', 'ulid' => Str::ulid(), 'email' => 'luxere20@gmail.com', 'date_registered' => Carbon::now()->toDateTimeString(),]
        );

        //Account 1003
        $account1003 = Account::query()->firstOrCreate(
            ['number' => 'ACCOUNT20251003'],
            ['number' => 'ACCOUNT20251003', 'ulid' => Str::ulid(), 'email' => 'luxere20@gmail.com', 'date_registered' => Carbon::now()->toDateTimeString(),]
        );

         //Account chain, role creation
        if($account1001->roles->isEmpty()){
            new AccountCreatedChain()->handle(new AccountCreated($account1001));
        }
        if($account1002->roles->isEmpty()){
            new AccountCreatedChain()->handle(new AccountCreated($account1002));
        }
        if($account1003->roles->isEmpty()){
            new AccountCreatedChain()->handle(new AccountCreated($account1003));
        }

        //Account admin role
        $account1001AdminRole = $account1001->roles()->where(['name' => 'Admin'])->first();
        $account1002AdminRole = $account1002->roles()->where(['name' => 'Admin'])->first();
        $account1003AdminRole = $account1003->roles()->where(['name' => 'Admin'])->first();

         //Account subscriptions
        if($account1001->subscriptions->isEmpty()){
            $account1001->subscriptions()->create(['module' => AccountSubscriptionModule::EMPLOYEE_PORTAL, 'plan' => AccountSubscriptionPlan::STANDARD, 'date_subscribed' => Carbon::now()->toDateTimeString()]);
            $account1001->subscriptions()->create(['module' => AccountSubscriptionModule::HR_PAYROLL, 'plan' => AccountSubscriptionPlan::STANDARD, 'date_subscribed' => Carbon::now()->toDateTimeString()]);
        }

        if($account1002->subscriptions->isEmpty()){
            $account1002->subscriptions()->create(['module' => AccountSubscriptionModule::EMPLOYEE_PORTAL, 'plan' => AccountSubscriptionPlan::STANDARD, 'date_subscribed' => Carbon::now()->toDateTimeString()]);
            $account1002->subscriptions()->create(['module' => AccountSubscriptionModule::HR_PAYROLL, 'plan' => AccountSubscriptionPlan::STANDARD, 'date_subscribed' => Carbon::now()->toDateTimeString()]);
            $account1002->subscriptions()->create(['module' => AccountSubscriptionModule::INVENTORY, 'plan' => AccountSubscriptionPlan::STANDARD, 'date_subscribed' => Carbon::now()->toDateTimeString()]);
            $account1002->subscriptions()->create(['module' => AccountSubscriptionModule::FINANCE_ACCOUNTING, 'plan' => AccountSubscriptionPlan::STANDARD, 'date_subscribed' => Carbon::now()->toDateTimeString()]);
        }

        if($account1003->subscriptions->isEmpty()){
            $account1003->subscriptions()->create(['module' => AccountSubscriptionModule::HR_PAYROLL, 'date_subscribed' => Carbon::now()->toDateTimeString()]);
        }

        $philippines = Country::query()->where('iso2', 'PH')->first();

        //Account 1001 Companies
        $companySCE = $account1001->companies()->firstOrCreate(['code' => 'SCE'],[
            'short_name' => 'SummitCore Enterprises', 'name' => 'SummitCore Enterprises', 'code' => 'SCE', 'country_id' => $philippines->id, 'currency' => 'PHP', 'timezone' => 'Asia/Manila', 'ulid' => Str::ulid(),
            'address_line_1' => '',
            'address_line_2' => '',
        ]);

        //Account 1002 Companies
        $companySMC = $account1002->companies()->firstOrCreate(['code' => 'SMC'],[
            'short_name' => 'Sterling Medical Center', 'name' => 'Sterling Medical Center', 'code' => 'SMC', 'country_id' => $philippines->id, 'currency' => 'PHP', 'timezone' => 'Asia/Manila', 'ulid' => Str::ulid(),
            'address_line_1' => '1234 Aurora Tech Park, Bonifacio Avenue',
            'address_line_2' => 'Taguig 1634, Philippines',
        ]);
        $companyVTC = $account1002->companies()->firstOrCreate(['code' => 'VTC'],[
            'short_name' => 'Veltrix Technologies .Co', 'name' => 'Veltrix Technologies .Co', 'code' => 'VTC', 'country_id' => $philippines->id, 'currency' => 'PHP', 'timezone' => 'Asia/Manila', 'ulid' => Str::ulid(),
            'address_line_1' => '77 Marina Bay Innovation Centre',
            'address_line_2' => 'Singapore 018956',
        ]);
        $companyMAC = $account1002->companies()->firstOrCreate(['code' => 'MAC'],[
            'short_name' => 'Meridian Axis Corp', 'name' => 'Meridian Axis Corp', 'code' => 'MAC', 'country_id' => $philippines->id, 'currency' => 'PHP', 'timezone' => 'Asia/Manila', 'ulid' => Str::ulid(),
            'address_line_1' => '88 King’s Cross Tower, Level 12',
            'address_line_2' => 'London WC1X 9DT, United Kingdom',
        ]);
        $companyDL = $account1002->companies()->firstOrCreate(['code' => 'DL'],[
            'short_name' => 'Driftwood Labs', 'name' => 'Driftwood Labs', 'code' => 'DL', 'country_id' => $philippines->id, 'currency' => 'PHP', 'timezone' => 'Asia/Manila', 'ulid' => Str::ulid(),
            'address_line_1' => '1200 Financial District Avenue',
            'address_line_2' => 'Toronto, ON M5H 2N2, Canada',
        ]);

        //Account 1001 Companies
        $companySC = $account1003->companies()->firstOrCreate(['code' => 'SC'],[
            'short_name' => 'Sunvale Creative', 'name' => 'Sunvale Creative', 'code' => 'SC', 'country_id' => $philippines->id, 'currency' => 'PHP', 'timezone' => 'Asia/Manila', 'ulid' => Str::ulid(),
            'address_line_1' => 'Block C, TechnoPark Ring Road',
            'address_line_2' => 'Bangalore 560103, India',
        ]);

        //Company approval settings
        foreach(ApprovalService::$seriesMap as $approvalSetting){
            $companySCE->approvalSettings()->firstOrCreate(['request_model' => $approvalSetting['model_alias']]);
            $companySMC->approvalSettings()->firstOrCreate(['request_model' => $approvalSetting['model_alias']]);
            $companyVTC->approvalSettings()->firstOrCreate(['request_model' => $approvalSetting['model_alias']]);
            $companyMAC->approvalSettings()->firstOrCreate(['request_model' => $approvalSetting['model_alias']]);
            $companyDL->approvalSettings()->firstOrCreate(['request_model' => $approvalSetting['model_alias']]);
            $companySC->approvalSettings()->firstOrCreate(['request_model' => $approvalSetting['model_alias']]);
        }

        //Account 1002User01
        $account1002User01 = User::query()->firstOrCreate(['name' => '1002.user.1'],[...User::factory()->definition(), 'name' => '1002.user.1', 'email' => 'luxere20@gmail.com', 'ulid' => Str::ulid(),]);
        $account1002User02 = User::query()->firstOrCreate(['name' => '1002.user.2'],[...User::factory()->definition(), 'name' => '1002.user.2', 'email' => 'luxere20@gmail.com', 'ulid' => Str::ulid(), 'created_by' => $account1002User01->id,]);
        $account1002User03 = User::query()->firstOrCreate(['name' => '1002.user.3'],[...User::factory()->definition(), 'name' => '1002.user.3', 'email' => 'luxere20@gmail.com', 'ulid' => Str::ulid(), 'created_by' => $account1002User01->id,]);
        $account1002User04 = User::query()->firstOrCreate(['name' => '1002.user.4'],[...User::factory()->definition(), 'name' => '1002.user.4', 'email' => 'luxere20@gmail.com', 'ulid' => Str::ulid(), 'created_by' => $account1002User01->id,]);
        $account1002User05 = User::query()->firstOrCreate(['name' => '1002.user.5'],[...User::factory()->definition(), 'name' => '1002.user.5', 'email' => 'luxere20@gmail.com', 'ulid' => Str::ulid(), 'created_by' => $account1002User01->id,]);
        $user11 = User::query()->firstOrCreate(['name' => 'user.11'],[...User::factory()->definition(), 'name' => 'user.11', 'email' => 'luxere20@gmail.com', 'ulid' => Str::ulid(), 'created_by' => $account1002User01->id,]);

        //Company MAC Shifts
        //Regular no lunch out/in
        $shiftMAC1 = $companyMAC->shifts()->firstOrCreate(['code' => '001-DAYSHIFT-REG-2DOFF-NL0/I'],['ulid' => Str::ulid(), 'code' => '001-DAYSHIFT-REG-2DOFF-NL0/I', 'name' => 'REGULAR 2 DAYS OFF[SUN,SAT] 09:00 AM to 05:00 PM NO LUNCH OUT/IN', 'type' => ShiftType::REGULAR, 'work_start_grace_time' => 10, 'require_lunch_time_in_and_out' => false, 'lunch_start_grace_time' => 0, 'max_overtime' => 0]);
        $this->createShiftSchedules(Shift::query()->where('code', '001-DAYSHIFT-REG-2DOFF-NL0/I')->first(), false, ['09:00','17:00'], '08:00', true, ['12:00','13:00'], '01:00', [CarbonInterface::SUNDAY, CarbonInterface::SATURDAY]);

        //Regular no lunch out/in 4.5 hours ot
        $shiftMAC2 = $companyMAC->shifts()->firstOrCreate(['code' => '001-DAYSHIFT-REG-2DOFF-NL0/I-6.5MOT'],['ulid' => Str::ulid(), 'code' => '001-DAYSHIFT-REG-2DOFF-NL0/I-6.5MOT', 'name' => 'REGULAR 2 DAYS OFF[SUN,SAT] 09:00 AM to 05:00 PM NO LUNCH OUT/IN 06:30 MAX OT', 'type' => ShiftType::REGULAR, 'work_start_grace_time' => 10, 'require_lunch_time_in_and_out' => false, 'lunch_start_grace_time' => 0, 'max_overtime' => 6.5, 'holiday_policy' => ShiftHolidayPolicy::ATTENDANCE_REQUIRED]);
        $this->createShiftSchedules(Shift::query()->where('code', '001-DAYSHIFT-REG-2DOFF-NL0/I-6.5MOT')->first(), false, ['09:00','17:00'], '08:00', true, ['12:00','13:00'], '01:00', [CarbonInterface::SUNDAY, CarbonInterface::SATURDAY]);

        //Regular no lunch out/in 10.5 hours ot
        $shiftMAC212HShift = $companyMAC->shifts()->firstOrCreate(['code' => '001-DAYSHIFT-REG-1DOFF-NL0/I-10.5MOT'],['ulid' => Str::ulid(), 'code' => '001-DAYSHIFT-REG-1DOFF-NL0/I-10.5MOT', 'name' => 'REGULAR 2 DAYS OFF[SUN,SAT] 04:00 AM to 04:00 PM NO LUNCH OUT/IN 10:30 MAX OT', 'type' => ShiftType::REGULAR, 'work_start_grace_time' => 10, 'require_lunch_time_in_and_out' => false, 'lunch_start_grace_time' => 0, 'max_overtime' => 10.5, 'holiday_policy' => ShiftHolidayPolicy::ATTENDANCE_REQUIRED]);
        $this->createShiftSchedules(Shift::query()->where('code', '001-DAYSHIFT-REG-1DOFF-NL0/I-10.5MOT')->first(), false, ['04:00','16:00'], '12:00', true, ['12:00','13:00'], '01:00', [CarbonInterface::SUNDAY]);

        //Regular with lunch out/in
        $shiftMAC3 = $companyMAC->shifts()->firstOrCreate(['code' => '002-DAYSHIFT-REG-2DOFF-WL0/I'],['ulid' => Str::ulid(), 'code' => '002-DAYSHIFT-REG-2DOFF-WL0/I', 'name' => 'REGULAR 2 DAYS OFF[SUN,SAT] 09:00 AM to 05:00 PM WITH LUNCH OUT/IN', 'type' => ShiftType::REGULAR, 'work_start_grace_time' => 10, 'require_lunch_time_in_and_out' => true, 'lunch_start_grace_time' => 10, 'max_overtime' => 0]);
        $this->createShiftSchedules(Shift::query()->where('code', '002-DAYSHIFT-REG-2DOFF-WL0/I')->first(), false, ['09:00','17:00'], '08:00', true, ['12:00','13:00'], '01:00', [CarbonInterface::SUNDAY, CarbonInterface::SATURDAY]);

        //Regular with lunch out/in 4.5 hours ot
        $shiftMAC4 = $companyMAC->shifts()->firstOrCreate(['code' => '002-DAYSHIFT-REG-2DOFF-WL0/I-6.5MOT'],['ulid' => Str::ulid(), 'code' => '002-DAYSHIFT-REG-2DOFF-WL0/I-6.5MOT', 'name' => 'REGULAR 2 DAYS OFF[SUN,SAT] 09:00 AM to 05:00 PM WITH LUNCH OUT/IN 06:30 MAX OT', 'type' => ShiftType::REGULAR, 'work_start_grace_time' => 10, 'require_lunch_time_in_and_out' => true, 'lunch_start_grace_time' => 10, 'max_overtime' => 6.5]);
        $this->createShiftSchedules(Shift::query()->where('code', '002-DAYSHIFT-REG-2DOFF-WL0/I-6.5MOT')->first(), false, ['09:00','17:00'], '08:00', true, ['12:00','13:00'], '01:00', [CarbonInterface::SUNDAY, CarbonInterface::SATURDAY]);

        //Regular flexible no lunch out/in
        $shiftMAC5 = $companyMAC->shifts()->firstOrCreate(['code' => '003-DAYSHIFT-FLEX-2DOFF-NL0/I'],['ulid' => Str::ulid(), 'code' => '003-DAYSHIFT-FLEX-2DOFF-NL0/I', 'name' => 'FLEXIBLE 2 DAYS OFF[SUN,SAT] 00:00 AM to 00:00 AM NO LUNCH OUT/IN', 'type' => ShiftType::REGULAR, 'work_start_grace_time' => 10, 'require_lunch_time_in_and_out' => false, 'lunch_start_grace_time' => 0, 'max_overtime' => 0]);
        $this->createShiftSchedules(Shift::query()->where('code', '003-DAYSHIFT-FLEX-2DOFF-NL0/I')->first(), true, ['00:00','00:00'], '09:00', true, ['12:00','13:00'], '01:00', [CarbonInterface::SUNDAY, CarbonInterface::SATURDAY]);

        //Regular flexible with lunch out/in
        $shiftMAC6 = $companyMAC->shifts()->firstOrCreate(['code' => '004-DAYSHIFT-FLEX-2DOFF-WL0/I'],['ulid' => Str::ulid(), 'code' => '004-DAYSHIFT-FLEX-2DOFF-WL0/I', 'name' => 'FLEXIBLE 2 DAYS OFF[SUN,SAT] 00:00 AM to 00:00 AM WITH LUNCH OUT/IN', 'type' => ShiftType::REGULAR, 'work_start_grace_time' => 10, 'require_lunch_time_in_and_out' => true, 'lunch_start_grace_time' => 10, 'max_overtime' => 0]);
        $this->createShiftSchedules(Shift::query()->where('code', '004-DAYSHIFT-FLEX-2DOFF-WL0/I')->first(), true, ['00:00','00:00'], '09:00', true, ['12:00','13:00'], '01:00', [CarbonInterface::SUNDAY, CarbonInterface::SATURDAY]);

        //Night midnight start no lunch out/in
        $shiftMAC7 = $companyMAC->shifts()->firstOrCreate(['code' => '005-GRAVEYARD-NHT-MIDNIGHT-2DOFF-NL0/I'],['ulid' => Str::ulid(), 'code' => '005-GRAVEYARD-NHT-MIDNIGHT-2DOFF-NL0/I', 'name' => 'GRAVEYARD 2 DAYS OFF[SUN,SAT] 00:00 AM to 10:00 AM NO LUNCH OUT/IN', 'type' => ShiftType::GRAVEYARD, 'work_start_grace_time' => 10, 'require_lunch_time_in_and_out' => false, 'lunch_start_grace_time' => 0, 'max_overtime' => 0]);
        $this->createShiftSchedules(Shift::query()->where('code', '005-GRAVEYARD-NHT-MIDNIGHT-2DOFF-NL0/I')->first(), false, ['00:00','10:00'], '10:00', true, ['01:00','02:00'], '01:00', [CarbonInterface::SUNDAY, CarbonInterface::SATURDAY]);

        //Night midnight start with lunch out/in
        $shiftMAC8 = $companyMAC->shifts()->firstOrCreate(['code' => '006-GRAVEYARD-NHT-MIDNIGHT-2DOFF-WL0/I'],['ulid' => Str::ulid(), 'code' => '006-GRAVEYARD-NHT-MIDNIGHT-2DOFF-WL0/I', 'name' => 'GRAVEYARD 2 DAYS OFF[SUN,SAT] 00:00 PM to 10:00 AM WITH LUNCH OUT/IN', 'type' => ShiftType::GRAVEYARD, 'work_start_grace_time' => 10, 'require_lunch_time_in_and_out' => true, 'lunch_start_grace_time' => 10, 'max_overtime' => 0]);
        $this->createShiftSchedules(Shift::query()->where('code', '006-GRAVEYARD-NHT-MIDNIGHT-2DOFF-WL0/I')->first(), false, ['00:00','10:00'], '10:00', true, ['01:00','02:00'], '01:00', [CarbonInterface::SUNDAY, CarbonInterface::SATURDAY]);

        //Night no lunch out/in
        $shiftMAC9 = $companyMAC->shifts()->firstOrCreate(['code' => '007-GRAVEYARD-NHT-2DOFF-NL0/I'],['ulid' => Str::ulid(), 'code' => '007-GRAVEYARD-NHT-2DOFF-NL0/I', 'name' => 'GRAVEYARD 2 DAYS OFF[SUN,SAT] 21:00 PM to 07:00 AM NO LUNCH OUT/IN', 'type' => ShiftType::GRAVEYARD, 'work_start_grace_time' => 10, 'require_lunch_time_in_and_out' => false, 'lunch_start_grace_time' => 0, 'max_overtime' => 0]);
        $this->createShiftSchedules(Shift::query()->where('code', '007-GRAVEYARD-NHT-2DOFF-NL0/I')->first(), false, ['21:00','07:00'], '10:00', true, ['01:00','02:00'], '01:00', [CarbonInterface::SUNDAY, CarbonInterface::SATURDAY]);

        //Night no lunch out/in 3 hours ot
        $shiftMAC10 = $companyMAC->shifts()->firstOrCreate(['code' => '007-GRAVEYARD-NHT-2DOFF-NL0/I-3MOT'],['ulid' => Str::ulid(), 'code' => '007-GRAVEYARD-NHT-2DOFF-NL0/I-3MOT', 'name' => 'GRAVEYARD 2 DAYS OFF[SUN,SAT] 21:00 PM to 04:00 AM NO LUNCH OUT/IN 03:00 MAX OT', 'type' => ShiftType::GRAVEYARD, 'work_start_grace_time' => 10, 'require_lunch_time_in_and_out' => false, 'lunch_start_grace_time' => 0, 'max_overtime' => 3]);
        $this->createShiftSchedules(Shift::query()->where('code', '007-GRAVEYARD-NHT-2DOFF-NL0/I-3MOT')->first(), false, ['21:00','04:00'], '07:00', true, ['01:00','02:00'], '01:00', [CarbonInterface::SUNDAY, CarbonInterface::SATURDAY]);

        //Night no lunch out/in 10 hours ot quadruple split
        $shiftMAC11 = $companyMAC->shifts()->firstOrCreate(['code' => '007-GRAVEYARD-NHT-2DOFF-NL0/I-10MOTQUADSPLIT'],['ulid' => Str::ulid(), 'code' => '007-GRAVEYARD-NHT-2DOFF-NL0/I-10MOTQUADSPLIT', 'name' => 'GRAVEYARD 2 DAYS OFF[SUN,SAT] 18:00 PM to 21:00 PM NO LUNCH OUT/IN 10:00 MAX OT QUADRUPLE SPLIT', 'type' => ShiftType::GRAVEYARD, 'work_start_grace_time' => 10, 'require_lunch_time_in_and_out' => false, 'lunch_start_grace_time' => 0, 'max_overtime' => 10]);
        $this->createShiftSchedules(Shift::query()->where('code', '007-GRAVEYARD-NHT-2DOFF-NL0/I-10MOTQUADSPLIT')->first(), false, ['18:00','21:00'], '03:00', true, ['19:00','20:00'], '01:00', [CarbonInterface::SUNDAY, CarbonInterface::SATURDAY]);

        //Night with lunch out/in
        $shiftMAC12 = $companyMAC->shifts()->firstOrCreate(['code' => '008-GRAVEYARD-NHT-2DOFF-WL0/I'],['ulid' => Str::ulid(), 'code' => '008-GRAVEYARD-NHT-2DOFF-WL0/I', 'name' => 'GRAVEYARD 2 DAYS OFF[SUN,SAT] 21:00 PM to 07:00 AM WITH LUNCH OUT/IN', 'type' => ShiftType::GRAVEYARD, 'work_start_grace_time' => 10, 'require_lunch_time_in_and_out' => true, 'lunch_start_grace_time' => 10, 'max_overtime' => 0]);
        $this->createShiftSchedules(Shift::query()->where('code', '008-GRAVEYARD-NHT-2DOFF-WL0/I')->first(), false, ['21:00','07:00'], '10:00', true, ['01:00','02:00'], '01:00', [CarbonInterface::SUNDAY, CarbonInterface::SATURDAY]);

        //Night with lunch out/in 3 hours ot
        $shiftMAC13 = $companyMAC->shifts()->firstOrCreate(['code' => '008-GRAVEYARD-NHT-2DOFF-WL0/I-3MOT'],['ulid' => Str::ulid(), 'code' => '008-GRAVEYARD-NHT-2DOFF-WL0/I-3MOT', 'name' => 'GRAVEYARD 2 DAYS OFF[SUN,SAT] 21:00 PM to 04:00 AM WITH LUNCH OUT/IN 03:00 MAX OT', 'type' => ShiftType::GRAVEYARD, 'work_start_grace_time' => 10, 'require_lunch_time_in_and_out' => true, 'lunch_start_grace_time' => 10, 'max_overtime' => 3]);
        $this->createShiftSchedules(Shift::query()->where('code', '008-GRAVEYARD-NHT-2DOFF-WL0/I-3MOT')->first(), false, ['21:00','04:00'], '07:00', true, ['01:00','02:00'], '01:00', [CarbonInterface::SUNDAY, CarbonInterface::SATURDAY]);

        //Night with lunch out/in 10 hours ot quadruple split
        $shiftMAC14 = $companyMAC->shifts()->firstOrCreate(['code' => '007-GRAVEYARD-NHT-2DOFF-WL0/I-10MOTQUADSPLIT'],['ulid' => Str::ulid(), 'code' => '007-GRAVEYARD-NHT-2DOFF-WL0/I-10MOTQUADSPLIT', 'name' => 'GRAVEYARD 2 DAYS OFF[SUN,SAT] 18:00 PM to 21:00 PM WITH LUNCH OUT/IN 10:00 MAX OT QUADRUPLE SPLIT', 'type' => ShiftType::GRAVEYARD, 'work_start_grace_time' => 10, 'require_lunch_time_in_and_out' => true, 'lunch_start_grace_time' => 0, 'max_overtime' => 10]);
        $this->createShiftSchedules(Shift::query()->where('code', '007-GRAVEYARD-NHT-2DOFF-WL0/I-10MOTQUADSPLIT')->first(), false, ['18:00','21:00'], '03:00', true, ['19:00','20:00'], '01:00', [CarbonInterface::SUNDAY, CarbonInterface::SATURDAY]);

        //Night no lunch out/in, lunch double split
        $shiftMAC15 = $companyMAC->shifts()->firstOrCreate(['code' => '009-GRAVEYARD-NHT-2DOFF-NL0/I-LUNCH-2-SPLIT'],['ulid' => Str::ulid(), 'code' => '009-GRAVEYARD-NHT-2DOFF-NL0/I-LUNCH-2-SPLIT', 'name' => 'GRAVEYARD 2 DAYS OFF[SUN,SAT] 21:00 PM to 07:00 AM NO LUNCH OUT/IN LUNCH DOUBLE SPLIT', 'type' => ShiftType::GRAVEYARD, 'work_start_grace_time' => 10, 'require_lunch_time_in_and_out' => false, 'lunch_start_grace_time' => 0, 'max_overtime' => 0]);
        $this->createShiftSchedules(Shift::query()->where('code', '009-GRAVEYARD-NHT-2DOFF-NL0/I-LUNCH-2-SPLIT')->first(), false, ['21:00','07:00'], '10:00', true, ['23:30','00:30'], '01:00', [CarbonInterface::SUNDAY, CarbonInterface::SATURDAY]);
        //Night with lunch out/in, lunch double split
        $shiftMAC16 = $companyMAC->shifts()->firstOrCreate(['code' => '010-GRAVEYARD-NHT-2DOFF-WL0/I-LUNCH-2-SPLIT'],['ulid' => Str::ulid(), 'code' => '010-GRAVEYARD-NHT-2DOFF-WL0/I-LUNCH-2-SPLIT', 'name' => 'GRAVEYARD 2 DAYS OFF[SUN,SAT] 21:00 PM to 07:00 AM WITH LUNCH OUT/IN LUNCH DOUBLE SPLIT', 'type' => ShiftType::GRAVEYARD, 'work_start_grace_time' => 10, 'require_lunch_time_in_and_out' => true, 'lunch_start_grace_time' => 10, 'max_overtime' => 0]);
        $this->createShiftSchedules(Shift::query()->where('code', '010-GRAVEYARD-NHT-2DOFF-WL0/I-LUNCH-2-SPLIT')->first(), false, ['21:00','07:00'], '10:00', true, ['23:30','00:30'], '01:00', [CarbonInterface::SUNDAY, CarbonInterface::SATURDAY]);

        //Night no lunch out/in, lunch triple split
        $shiftMAC17 = $companyMAC->shifts()->firstOrCreate(['code' => '011-GRAVEYARD-NHT-2DOFF-NL0/I-LUNCH-3-SPLIT'],['ulid' => Str::ulid(), 'code' => '011-GRAVEYARD-NHT-2DOFF-NL0/I-LUNCH-3-SPLIT', 'name' => 'GRAVEYARD 2 DAYS OFF[SUN,SAT] 21:00 PM to 07:00 AM NO LUNCH OUT/IN LUNCH TRIPLE SPLIT', 'type' => ShiftType::GRAVEYARD, 'work_start_grace_time' => 10, 'require_lunch_time_in_and_out' => false, 'lunch_start_grace_time' => 0, 'max_overtime' => 0]);
        $this->createShiftSchedules(Shift::query()->where('code', '011-GRAVEYARD-NHT-2DOFF-NL0/I-LUNCH-3-SPLIT')->first(), false, ['21:00','07:00'], '10:00', true, ['21:30','00:30'], '01:00', [CarbonInterface::SUNDAY, CarbonInterface::SATURDAY]);
        //Night with lunch out/in, lunch triple split
        $shiftMAC18 = $companyMAC->shifts()->firstOrCreate(['code' => '012-GRAVEYARD-NHT-2DOFF-WL0/I-LUNCH-3-SPLIT'],['ulid' => Str::ulid(), 'code' => '012-GRAVEYARD-NHT-2DOFF-WL0/I-LUNCH-3-SPLIT', 'name' => 'GRAVEYARD 2 DAYS OFF[SUN,SAT] 21:00 PM to 07:00 AM WITH LUNCH OUT/IN LUNCH TRIPLE SPLIT', 'type' => ShiftType::GRAVEYARD, 'work_start_grace_time' => 10, 'require_lunch_time_in_and_out' => true, 'lunch_start_grace_time' => 10, 'max_overtime' => 0]);
        $this->createShiftSchedules(Shift::query()->where('code', '012-GRAVEYARD-NHT-2DOFF-WL0/I-LUNCH-3-SPLIT')->first(), false, ['21:00','07:00'], '10:00', true, ['21:30','00:30'], '01:00', [CarbonInterface::SUNDAY, CarbonInterface::SATURDAY]);

        //Company MAC Leave types
        $leaveMAC1 = $companyMAC->leaveTypes()->firstOrCreate([
            'code' => 'PVL'
        ],[
            'ulid' => Str::ulid(),
            'code' => 'PVL',
            'name' => 'Vacation leave w/ pay',
            'type' => LeaveTypeEnum::VACATION,
            'is_paid' => true,
            'monetizable' => false,

            'limit_usage' => true,
            'limit_usage_span_type' => LeaveUsageSpanType::MONTH,
            'limit_usage_span_value' => 1,
            'limit_usage_value' => 5,

            'eligibility_employment_types' => [EmploymentType::NOT_SPECIFIED, EmploymentType::FULL_TIME],
            'initial_balance_upon_eligibility' => 14,

            'period_type' => LeavePeriodType::CALENDAR_YEAR,
            'period_calendar_span_value' => 1,

            'carry_over_balance_per_new_period' => true,
            'carry_over_balance_type' => LeaveCarryOverType::LIMIT,
            'carry_over_balance_value' => 7,
        ]);

        if($leaveMAC1->balancePerPeriod->isEmpty()){
            $leaveMAC1->balancePerPeriod()->create(['from_period' => 2, 'to_period' => 4, 'balance' => 2]);
            $leaveMAC1->balancePerPeriod()->create(['from_period' => 5, 'to_period' => 5, 'balance' => 5]);
            $leaveMAC1->balancePerPeriod()->create(['from_period' => 6, 'balance' => 10]);
        }

        $leaveMAC2 = $companyMAC->leaveTypes()->firstOrCreate([
            'code' => 'SL'
        ],[
            'ulid' => Str::ulid(),
            'code' => 'SL',
            'name' => 'Sick leave',
            'type' => LeaveTypeEnum::SICK,
            'is_paid' => false,
            'monetizable' => false,

            'limit_usage' => false,

            'eligibility_employment_types' => [EmploymentType::NOT_SPECIFIED, EmploymentType::CONTRACT, EmploymentType::FULL_TIME],
            'initial_balance_upon_eligibility' => 6,

            'period_type' => LeavePeriodType::CALENDAR_YEAR,
            'period_calendar_span_value' => 1,

            'carry_over_balance_per_new_period' => false,
        ]);

        if($leaveMAC2->balancePerPeriod->isEmpty()){
            $leaveMAC2->balancePerPeriod()->create(['from_period' => 1, 'balance' => 6]);
        }

        $leaveMAC3 = $companyMAC->leaveTypes()->firstOrCreate([
            'code' => 'EL'
        ],[
            'ulid' => Str::ulid(),
            'code' => 'EL',
            'name' => 'Emergency leave',
            'type' => LeaveTypeEnum::EMERGENCY,
            'is_paid' => false,
            'monetizable' => false,

            'limit_usage' => true,
            'limit_usage_span_type' => LeaveUsageSpanType::DAY,
            'limit_usage_span_value' => 42,
            'limit_usage_value' => 5,

            'eligibility_employment_types' => [EmploymentType::NOT_SPECIFIED, EmploymentType::CONTRACT, EmploymentType::FULL_TIME],
            'initial_balance_upon_eligibility' => 2,

            'period_type' => LeavePeriodType::INTERVAL,
            'period_interval_span_type' => LeaveIntervalSpanType::MONTH,
            'period_interval_span_value' => 6,

            'carry_over_balance_per_new_period' => true,
            'carry_over_balance_type' => LeaveCarryOverType::LIMIT,
            'carry_over_balance_value' => 6,
        ]);

        if($leaveMAC3->balancePerPeriod->isEmpty()){
            $leaveMAC3->balancePerPeriod()->create(['from_period' => 2, 'to_period' => 2, 'balance' => 3]);
            $leaveMAC3->balancePerPeriod()->create(['from_period' => 3, 'balance' => 6]);
        }

        $leaveMAC4 = $companyMAC->leaveTypes()->firstOrCreate([
            'code' => 'CL'
        ],[
            'ulid' => Str::ulid(),
            'code' => 'CL',
            'name' => 'Casual leave',
            'type' => LeaveTypeEnum::VACATION,
            'is_paid' => false,
            'monetizable' => false,

            'limit_usage' => true,
            'limit_usage_span_type' => LeaveUsageSpanType::MONTH,
            'limit_usage_span_value' => 3,
            'limit_usage_value' => 5,

            'eligibility_employment_types' => [EmploymentType::FULL_TIME],
            'initial_balance_upon_eligibility' => 5,

            'period_type' => LeavePeriodType::CALENDAR_YEAR,
            'period_calendar_span_value' => 1,

            'carry_over_balance_per_new_period' => true,
            'carry_over_balance_type' => LeaveCarryOverType::LIMIT,
            'carry_over_balance_value' => 6,
        ]);

        if($leaveMAC4->balancePerPeriod->isEmpty()){
            $leaveMAC4->balancePerPeriod()->create(['from_period' => 1, 'to_period' => 1, 'balance' => 0]);
            $leaveMAC4->balancePerPeriod()->create(['from_period' => 2, 'balance' => 2]);
            $leaveMAC4->balancePerPeriod()->create(['from_period' => 3, 'balance' => 4]);
        }

        /*
         * Employee: has employee info and default assigned to a company
         * Employee Admin: has employee info and admin assigned to a company
         * Admin: no employee info and admin assigned to a company
         * */

        //Assign 1002User01 to Company SCE as Admin
        $account1002User01->companies()->detach();
        $account1002User01->companies()->syncWithoutDetaching([$companySCE->id => ['assignment_type' => CompanyUserAssignmentType::ADMIN]]);

        //Assign 1002User01 to Company SMC as Employee
        $account1002User01->companies()->syncWithoutDetaching([$companySMC->id => ['assignment_type' => CompanyUserAssignmentType::DEFAULT]]);


        //Assign 1002User01 to Company VTC as Admin
        $account1002User01->companies()->syncWithoutDetaching([$companyVTC->id => ['assignment_type' => CompanyUserAssignmentType::ADMIN]]);

        //Assign 1002User02 to Company VTC as Employee
        $account1002User02->companies()->detach();
        $account1002User02->companies()->syncWithoutDetaching([$companyVTC->id => ['assignment_type' => CompanyUserAssignmentType::DEFAULT]]);


        //Assign 1002User01 to Company MAC as Employee Admin
        $account1002User01->companies()->syncWithoutDetaching([$companyMAC->id => ['assignment_type' => CompanyUserAssignmentType::ADMIN]]);

        //Assign 1002User02 to Company MAC as Employee Admin
        $account1002User02->companies()->syncWithoutDetaching([$companyMAC->id => ['assignment_type' => CompanyUserAssignmentType::ADMIN]]);

        //Assign 1002User03 to Company MAC as Employee
        $account1002User03->companies()->detach();
        $account1002User03->companies()->syncWithoutDetaching([$companyMAC->id => ['assignment_type' => CompanyUserAssignmentType::DEFAULT]]);

        //Assign 1002User04 to Company MAC as Admin
        $account1002User04->companies()->detach();
        $account1002User04->companies()->syncWithoutDetaching([$companyMAC->id => ['assignment_type' => CompanyUserAssignmentType::ADMIN]]);

        //Assign 1002User04 to Company DL as Admin
        $account1002User04->companies()->syncWithoutDetaching([$companyDL->id => ['assignment_type' => CompanyUserAssignmentType::ADMIN]]);

        //Assign 1002User05 to Company MAC as Employee
        $account1002User05->companies()->detach();
        $account1002User05->companies()->syncWithoutDetaching([$companyMAC->id => ['assignment_type' => CompanyUserAssignmentType::DEFAULT]]);

        //Assign Admin role of account 1001 and 1002 to 1002User01
        if($account1002User01->roles->isEmpty()){
            $account1002User01->syncRoles([$account1001AdminRole, $account1002AdminRole]);
        }

        //Assign Admin role of account 1002 to 1002User02
        if($account1002User02->roles->isEmpty()){
            $account1002User02->syncRoles([$account1002AdminRole]);
        }
        /**************************************************************************************************************************************************************************************************************/

        //Company VTC, MAC Salary Statement Modules

        foreach (App::make(SalaryStatementModuleRepository::class)->defaultPresets() as $salaryStatementModule) {

            $companyVTC->salaryStatementModules()->firstOrCreate([
                'name' => $salaryStatementModule['name'],
                'formulable_type' => $salaryStatementModule['formulable_type']
            ], $salaryStatementModule);

            $companyMAC->salaryStatementModules()->firstOrCreate([
                'name' => $salaryStatementModule['name'],
                'formulable_type' => $salaryStatementModule['formulable_type']
            ], $salaryStatementModule);
        }

        $formulas = Formula::query()->whereNotIn('name', ['Standard-Tardiness', 'Standard-Undertime', 'Standard-Absence'])->get();

        //Company SMC, VTC, MAC Assign Formula Presets
        $companySMC->formulas()->detach();
        $companyVTC->formulas()->detach();
        $companyMAC->formulas()->detach();

        foreach ($formulas as $formula) {

            $settings = empty($formula->default_settings?->cast) ? null : json_encode($formula->default_settings?->cast);

            $companySMC->formulas()->syncWithoutDetaching([$formula->id => ['settings' => $settings]]);
            $companyVTC->formulas()->syncWithoutDetaching([$formula->id => ['settings' => $settings]]);
            $companyMAC->formulas()->syncWithoutDetaching([$formula->id => ['settings' => $settings]]);
        }

        // Company SCE, SMC, VTC, MAC, and DL Pay Frequencies
        foreach (App::make(PayFrequencyRepository::class)->defaultPresets() as $payFrequency) {
            $companySCE->payFrequencies()->firstOrCreate(['code' => $payFrequency['code']], ['ulid' => Str::ulid(), ...$payFrequency]);
            $companySMC->payFrequencies()->firstOrCreate(['code' => $payFrequency['code']], ['ulid' => Str::ulid(), ...$payFrequency]);
            $companyVTC->payFrequencies()->firstOrCreate(['code' => $payFrequency['code']], ['ulid' => Str::ulid(), ...$payFrequency]);
            $companyMAC->payFrequencies()->firstOrCreate(['code' => $payFrequency['code']], ['ulid' => Str::ulid(), ...$payFrequency]);
            $companyDL->payFrequencies()->firstOrCreate(['code' => $payFrequency['code']], ['ulid' => Str::ulid(), ...$payFrequency]);
        }

        //Company VTC Monthly Pay Frequency
        $companyVTCMonthlyPayFrequency = $companyVTC->payFrequencies()->where('code', 'MONTHLY')->first();
        //Company MAC Monthly Pay Frequency
        $companyMACMonthlyPayFrequency = $companyMAC->payFrequencies()->where('code', 'MONTHLY')->first();

        //Company VTC, MAC Pre-create Compensations
        $compensationsPresets = [
            ['code' => 'BASICPAY', 'name' => 'Basic pay', 'assignable' => true, 'type' => Compensation::BASIC_PAY, 'formula' => 'Standard-Basic-Pay'],
            ['code' => 'MEAL', 'name' => 'Meal allowance', 'assignable' => true, 'type' => Compensation::REGULAR_ALLOWANCE, 'formula' => 'Standard-Allowance'],
            ['code' => 'COFFEE', 'name' => 'Coffee allowance', 'assignable' => true, 'type' => Compensation::REGULAR_ALLOWANCE, 'formula' => 'Standard-Allowance'],
            ['code' => 'TRANSPORTATION', 'name' => 'Transportation allowance', 'assignable' => true, 'type' => Compensation::REGULAR_ALLOWANCE, 'formula' => 'Standard-Allowance'],
            ['code' => 'OVERTIME', 'name' => 'Overtime', 'assignable' => true, 'type' => Compensation::OVERTIME, 'formula' => 'Standard-Overtime'],
            ['code' => 'LEAVE-PAY', 'name' => 'Leave pay', 'assignable' => false, 'type' => Compensation::LEAVE_PAY, 'formula' => 'Standard-Leave-Pay'],
            ['code' => 'HOLIDAY-PAY', 'name' => 'Holiday pay', 'assignable' => false, 'type' => Compensation::HOLIDAY_PAY, 'formula' => 'Standard-Holiday-Pay'],
            ['code' => '13THMONTH', 'name' => '13th month pay', 'assignable' => true, 'type' => Compensation::BENEFIT, 'formula' => 'Standard-13th-Month'],
        ];

        foreach ($compensationsPresets as $index => $compensationPreset) {
            $this->createPayrollComponent($companyVTC, $index, Formulable::EARNINGS, 'compensations', $compensationPreset);
            $this->createPayrollComponent($companyMAC, $index, Formulable::EARNINGS, 'compensations', $compensationPreset);
        }

        //Company VTC, MAC Pre-create Deductions
        $deductionsPresets = [
            ['code' => 'TARDINESS', 'name' => 'Tardiness', 'assignable' => true, 'type' => Deduction::DEDUCTION, 'formula' => 'Standard-Tardiness'],
            ['code' => 'UNDERTIME', 'name' => 'Undertime', 'assignable' => true, 'type' => Deduction::DEDUCTION, 'formula' => 'Standard-Undertime'],
            ['code' => 'ABSENCE', 'name' => 'Absence', 'assignable' => true, 'type' => Deduction::DEDUCTION, 'formula' => 'Standard-Absence'],
            ['code' => 'SSS-EMPLOYED', 'name' => 'SSS contribution', 'assignable' => true, 'type' => Deduction::STATUTORY_CONTRIBUTION, 'formula' => 'Standard-SSS-Employed-Contribution'],
            ['code' => 'PHILHEALTH', 'name' => 'Philhealth (PHIC)', 'assignable' => true, 'type' => Deduction::STATUTORY_CONTRIBUTION, 'formula' => 'Standard-Philhealth-Contribution'],
            ['code' => 'PAG-IBIG', 'name' => 'Pag-IBIG (HDMF)', 'assignable' => true, 'type' => Deduction::STATUTORY_CONTRIBUTION, 'formula' => 'Standard-Pag-IBIG-Contribution'],
        ];

        foreach ($deductionsPresets as $index => $deductionsPreset) {
            $this->createPayrollComponent($companyVTC, $index, Formulable::DEDUCTIONS, 'deductions', $deductionsPreset);
            $this->createPayrollComponent($companyMAC, $index, Formulable::DEDUCTIONS, 'deductions', $deductionsPreset);
        }

        //Company VTC, MAC Pre-create Income Taxes
        $incomeTaxesPresets = [
            ['code' => 'WTC ', 'name' => 'Compensation tax (WTC)', 'assignable' => true, 'type' => IncomeTax::WITHHOLDING_TAX, 'formula' => 'Standard-Withholding-Tax-Compensation'],
        ];

        foreach ($incomeTaxesPresets as $index => $incomeTaxesPreset) {
            $this->createPayrollComponent($companyVTC, $index, Formulable::INCOME_TAX, 'incomeTaxes', $incomeTaxesPreset);
            $this->createPayrollComponent($companyMAC, $index, Formulable::INCOME_TAX, 'incomeTaxes', $incomeTaxesPreset);
        }

        /**************************************************************************************************************************************************************************************************************/

        //Create Departments to Company VTC
        $companyVTCExecutiveDepartment = $companyVTC->departments()->firstOrCreate(['name' => 'Executive']);
        $companyVTCHrDepartment = $companyVTC->departments()->firstOrCreate(['name' => 'HR']);
        $companyVTCPayrollDepartment = $companyVTC->departments()->firstOrCreate(['name' => 'Payroll'], ['name' => 'Payroll', 'parent_id' => $companyVTCHrDepartment->id]);
        $companyVTCTrainingAndDevelopmentDepartment = $companyVTC->departments()->firstOrCreate(['name' => 'Training & Development'], ['name' => 'Training & Development', 'parent_id' => $companyVTCHrDepartment->id]);
        $companyVTCFinanceAndAccountingDepartment = $companyVTC->departments()->firstOrCreate(['name' => 'Finance & Accounting']);
        $companyVTCAccountsPayableDepartment = $companyVTC->departments()->firstOrCreate(['name' => 'Accounts Payable'], ['name' => 'Accounts Payable', 'parent_id' => $companyVTCFinanceAndAccountingDepartment->id]);
        $companyVTCInternalAuditDepartment = $companyVTC->departments()->firstOrCreate(['name' => 'Internal Audit'], ['name' => 'Internal Audit', 'parent_id' => $companyVTCFinanceAndAccountingDepartment->id]);

        //Create Departments to Company MAC
        $companyMACExecutiveDepartment = $companyMAC->departments()->firstOrCreate(['name' => 'Executive']);
        $companyMACHrDepartment = $companyMAC->departments()->firstOrCreate(['name' => 'HR']);
        $companyMACPayrollDepartment = $companyMAC->departments()->firstOrCreate(['name' => 'Payroll'], ['name' => 'Payroll', 'parent_id' => $companyMACHrDepartment->id]);
        $companyMACTrainingAndDevelopmentDepartment = $companyMAC->departments()->firstOrCreate(['name' => 'Training & Development'], ['name' => 'Training & Development', 'parent_id' => $companyMACHrDepartment->id]);
        $companyMACFinanceAndAccountingDepartment = $companyMAC->departments()->firstOrCreate(['name' => 'Finance & Accounting']);
        $companyMACAccountsPayableDepartment = $companyMAC->departments()->firstOrCreate(['name' => 'Accounts Payable'], ['name' => 'Accounts Payable', 'parent_id' => $companyMACFinanceAndAccountingDepartment->id]);
        $companyMACInternalAuditDepartment = $companyMAC->departments()->firstOrCreate(['name' => 'Internal Audit'], ['name' => 'Internal Audit', 'parent_id' => $companyMACFinanceAndAccountingDepartment->id]);

        /**************************************************************************************************************************************************************************************************************/

        //Create Designations to Company VTC
        $companyVTC->designations()->firstOrCreate(['name' => 'CEO']);
        $companyVTCHrManager = $companyVTC->designations()->firstOrCreate(['name' => 'HR Manager']);
        $companyVTCHrAssistant = $companyVTC->designations()->firstOrCreate(['name' => 'HR Assistant']);
        $companyVTCAccountManager = $companyVTC->designations()->firstOrCreate(['name' => 'Account Manager']);
        $companyVTCPayrollManager = $companyMAC->designations()->firstOrCreate(['name' => 'Payroll Manager']);
        $companyVTCPayrollAssistant = $companyMAC->designations()->firstOrCreate(['name' => 'Payroll Assistant']);
        $companyVTCAccountingStaff = $companyVTC->designations()->firstOrCreate(['name' => 'Accounting Staff']);

        //Create Designations to Company MAC
        $companyMAC->designations()->firstOrCreate(['name' => 'CEO']);
        $companyMACHrManager = $companyMAC->designations()->firstOrCreate(['name' => 'HR Manager']);
        $companyMACHrAssistant = $companyMAC->designations()->firstOrCreate(['name' => 'HR Assistant']);
        $companyMACAccountManager = $companyMAC->designations()->firstOrCreate(['name' => 'Account Manager']);
        $companyMACPayrollManager = $companyMAC->designations()->firstOrCreate(['name' => 'Payroll Manager']);
        $companyMACPayrollAssistant = $companyMAC->designations()->firstOrCreate(['name' => 'Payroll Assistant']);
        $companyMACAccountingStaff = $companyMAC->designations()->firstOrCreate(['name' => 'Accounting Staff']);

        /**************************************************************************************************************************************************************************************************************/
        //Create Employee A1001 Info, Contact and Employment Profile to Company SMC
        $employeeA1001 = $this->createEmployee(
            $companySMC,
            $account1002User01,
            null,
            null,
            null,
            null,
            null,
            'A1001', 'Maëlle', 'A', 'Le Bris');
        $this->createEmployeeContact($employeeA1001);
        $this->createEmploymentProfile($employeeA1001);
        /**************************************************************************************************************************************************************************************************************/
        //Create Employee B1001 Info, Contact and Employment Profile
        $employeeB1001 = $this->createEmployee(
            $companyVTC,
            $account1002User02,
            $companyVTCHrDepartment,
            DepartmentEmployeeAssignmentType::HEAD,
            $companyVTCHrManager,
            null,
            $companyVTCMonthlyPayFrequency,
            'B1001',
            'Dubois', 'B', 'Anaïs');
        $this->createEmployeeContact($employeeB1001);
        $this->createEmploymentProfile($employeeB1001);
        /**************************************************************************************************************************************************************************************************************/
        //Create Employee C1001 Info, Contact, Employment Profile, Shift and Leave assignment
        $employeeC1001 = $this->createEmployee(
            $companyMAC,
            $account1002User01,
            $companyMACHrDepartment,
            DepartmentEmployeeAssignmentType::DEFAULT,
            $companyMACHrAssistant,
            null,
            $companyMACMonthlyPayFrequency,
            'C1001', 'Amanda', 'N', 'Nõrth');
        $this->createEmployeeContact($employeeC1001);
        $this->createEmploymentProfile($employeeC1001);
        $employeeC1001->shifts()->detach();
        $employeeC1001->shifts()->syncWithoutDetaching([$shiftMAC1->id => ['start_date' => '2025-01-10', 'stated_shift_end_date' => false,]]);
        $employeeC1001->leaveTypes()->detach();
        $employeeC1001->leaveTypes()->syncWithoutDetaching([
            $leaveMAC1->id => ['override_balance_upon_eligibility' => true, 'balance_upon_eligibility' => 0],
            $leaveMAC2->id
        ]);
        /**************************************************************************************************************************************************************************************************************/
        //Create Employee C1002 Info, Contact, Employment Profile, Shift, Leave assignment, adjustment and claims
        $employeeC1002 = $this->createEmployee(
            $companyMAC,
            $account1002User02,
            $companyMACFinanceAndAccountingDepartment,
            DepartmentEmployeeAssignmentType::DEFAULT,
            $companyMACAccountingStaff,
            $employeeC1001,
            $companyMACMonthlyPayFrequency,
            'C1002', 'Palamuérta', 'C', 'Scañtily');
        $this->createEmployeeContact($employeeC1002);
        $employeeC1002->employmentProfiles()->firstOrCreate([
            'status' => EmploymentStatus::ACTIVE,
            'employment_type' => EmploymentType::PROBATIONARY,
            'start_date' => '2025-01-01',
            'end_of_service_type' => EndOfServiceType::END_OF_CONTRACT,
            'end_date' => '2025-03-31'
        ]);
        $employeeC1002->employmentProfiles()->firstOrCreate([
            'status' => EmploymentStatus::ACTIVE,
            'employment_type' => EmploymentType::FULL_TIME,
            'start_date' => '2025-04-01',
            'end_of_service_type' => EndOfServiceType::END_OF_CONTRACT,
            'end_date' => '2029-11-30'
        ]);
        $employeeC1002->shifts()->detach();
        $employeeC1002->shifts()->syncWithoutDetaching([$shiftMAC212HShift->id => ['start_date' => '2025-01-10', 'stated_shift_end_date' => false,]]);
        $employeeC1002->leaveTypes()->detach();
        $employeeC1002->leaveTypes()->syncWithoutDetaching([
            $leaveMAC1->id,
            $leaveMAC4->id,
        ]);
        $employeeC1002->leaveBalanceAdjustments()->delete();
        $employeeC1002->leaveBalanceAdjustments()->create([
            'ulid' => Str::ulid(),
            'leave_type_id' => $leaveMAC4->id,
            'type' => LeaveBalanceAdjustmentType::DEDUCT,
            'balance' => 2,
            'effective_date' => '2026-01-01'
        ]);
        $employeeC1002->leaveBalanceAdjustments()->create([
            'ulid' => Str::ulid(),
            'leave_type_id' => $leaveMAC4->id,
            'type' => LeaveBalanceAdjustmentType::DEDUCT,
            'balance' => 3,
            'effective_date' => '2026-01-01'
        ]);
        $employeeC1002->leaveBalanceAdjustments()->create([
            'ulid' => Str::ulid(),
            'leave_type_id' => $leaveMAC4->id,
            'type' => LeaveBalanceAdjustmentType::DEDUCT,
            'balance' => 1,
            'effective_date' => '2027-01-01'
        ]);

        $employeeC1002->leaveBalanceAdjustments()->create([
            'ulid' => Str::ulid(),
            'leave_type_id' => $leaveMAC4->id,
            'type' => LeaveBalanceAdjustmentType::ADD,
            'balance' => 1,
            'effective_date' => '2026-05-21'
        ]);
        $employeeC1002->leaveBalanceAdjustments()->create([
            'ulid' => Str::ulid(),
            'leave_type_id' => $leaveMAC4->id,
            'type' => LeaveBalanceAdjustmentType::ADD,
            'balance' => 3,
            'effective_date' => '2026-05-21'
        ]);

        $employeeC1002->leaveBalanceAdjustments()->create([
            'ulid' => Str::ulid(),
            'leave_type_id' => $leaveMAC4->id,
            'type' => LeaveBalanceAdjustmentType::ADD,
            'balance' => 7,
            'effective_date' => '2027-05-21'
        ]);
        $employeeC1002->leaveBalanceAdjustments()->create([
            'ulid' => Str::ulid(),
            'leave_type_id' => $leaveMAC4->id,
            'type' => LeaveBalanceAdjustmentType::ADD,
            'balance' => 3,
            'effective_date' => '2027-05-21'
        ]);
        $employeeC1002->leaveBalanceAdjustments()->create([
            'ulid' => Str::ulid(),
            'leave_type_id' => $leaveMAC4->id,
            'type' => LeaveBalanceAdjustmentType::ADD,
            'balance' => 1,
            'effective_date' => '2027-09-21'
        ]);
        $employeeC1002->leaveBalanceAdjustments()->create([
            'ulid' => Str::ulid(),
            'leave_type_id' => $leaveMAC4->id,
            'type' => LeaveBalanceAdjustmentType::ADD,
            'balance' => 30,
            'effective_date' => '2026-01-26'
        ]);

        $employeeC1002->leaves()->delete();
        $employeeC1002->leaves()->create(['ulid' => Str::ulid(), 'leave_type_id' => $leaveMAC1->id, 'date' => '2026-01-28']);
        $employeeC1002->leaves()->create(['ulid' => Str::ulid(), 'leave_type_id' => $leaveMAC4->id, 'date' => '2026-01-29']);
        $employeeC1002->leaves()->create(['ulid' => Str::ulid(), 'leave_type_id' => $leaveMAC1->id, 'date' => '2026-02-02']);
        $employeeC1002->leaves()->create(['ulid' => Str::ulid(), 'leave_type_id' => $leaveMAC4->id, 'date' => '2026-02-03']);
        $employeeC1002->leaves()->create(['ulid' => Str::ulid(), 'leave_type_id' => $leaveMAC1->id, 'date' => '2026-02-05']);
        $employeeC1002->leaves()->create(['ulid' => Str::ulid(), 'leave_type_id' => $leaveMAC4->id, 'date' => '2026-02-06']);
        $employeeC1002->leaves()->create(['ulid' => Str::ulid(), 'leave_type_id' => $leaveMAC1->id, 'date' => '2026-02-19']);
        $employeeC1002->leaves()->create(['ulid' => Str::ulid(), 'leave_type_id' => $leaveMAC4->id, 'date' => '2026-02-20']);
        $employeeC1002->leaves()->create(['ulid' => Str::ulid(), 'leave_type_id' => $leaveMAC1->id, 'date' => '2026-03-05']);
        $employeeC1002->leaves()->create(['ulid' => Str::ulid(), 'leave_type_id' => $leaveMAC1->id, 'date' => '2026-03-11']);
        $employeeC1002->leaves()->create(['ulid' => Str::ulid(), 'leave_type_id' => $leaveMAC4->id, 'date' => '2026-03-13']);
        $employeeC1002->leaves()->create(['ulid' => Str::ulid(), 'leave_type_id' => $leaveMAC1->id, 'date' => '2026-03-23']);

        $employeeC1002->leaves()->create(['ulid' => Str::ulid(), 'leave_type_id' => $leaveMAC4->id, 'date' => '2027-05-21']);
        $employeeC1002->leaves()->create(['ulid' => Str::ulid(), 'leave_type_id' => $leaveMAC4->id, 'date' => '2027-05-22']);
        $employeeC1002->leaves()->create(['ulid' => Str::ulid(), 'leave_type_id' => $leaveMAC4->id, 'date' => '2027-05-23']);
        $employeeC1002->leaves()->create(['ulid' => Str::ulid(), 'leave_type_id' => $leaveMAC4->id, 'date' => '2027-05-24']);
        $employeeC1002->leaves()->create(['ulid' => Str::ulid(), 'leave_type_id' => $leaveMAC4->id, 'date' => '2027-05-25']);
        $employeeC1002->leaves()->create(['ulid' => Str::ulid(), 'leave_type_id' => $leaveMAC4->id, 'date' => '2027-05-26']);
        $employeeC1002->leaves()->create(['ulid' => Str::ulid(), 'leave_type_id' => $leaveMAC4->id, 'date' => '2027-05-27']);
        $employeeC1002->leaves()->create(['ulid' => Str::ulid(), 'leave_type_id' => $leaveMAC4->id, 'date' => '2027-05-28']);
        $employeeC1002->leaves()->create(['ulid' => Str::ulid(), 'leave_type_id' => $leaveMAC4->id, 'date' => '2027-05-29']);
        $employeeC1002->leaves()->create(['ulid' => Str::ulid(), 'leave_type_id' => $leaveMAC4->id, 'date' => '2027-09-20']);

        $employeeC1002->leaves()->create(['ulid' => Str::ulid(), 'leave_type_id' => $leaveMAC4->id, 'date' => '2027-12-01']);
        $employeeC1002->leaves()->create(['ulid' => Str::ulid(), 'leave_type_id' => $leaveMAC4->id, 'date' => '2027-12-02']);
        $employeeC1002->leaves()->create(['ulid' => Str::ulid(), 'leave_type_id' => $leaveMAC4->id, 'date' => '2027-12-03']);
        $employeeC1002->leaves()->create(['ulid' => Str::ulid(), 'leave_type_id' => $leaveMAC4->id, 'date' => '2027-12-04']);
        $employeeC1002->leaves()->create(['ulid' => Str::ulid(), 'leave_type_id' => $leaveMAC4->id, 'date' => '2027-12-05']);

        /**************************************************************************************************************************************************************************************************************/
        //Create Employee C1003 Info, Contact, Employment Profile and Shift
        $employeeC1003 = $this->createEmployee(
            $companyMAC,
            $account1002User03,
            $companyMACFinanceAndAccountingDepartment,
            DepartmentEmployeeAssignmentType::HEAD,
            $companyMACAccountManager,
            null,
            $companyMACMonthlyPayFrequency,
            'C1003', 'L’Écuyer', 'W', 'François');
        $this->createEmployeeContact($employeeC1003);
        $this->createEmploymentProfile($employeeC1003, EmploymentType::CONTRACT);
        $employeeC1003->shifts()->detach();
        $employeeC1003->shifts()->syncWithoutDetaching([$shiftMAC3->id => ['start_date' => '2025-01-10', 'stated_shift_end_date' => false,]]);

        //Create Employee C1004 Info, Contact, Employment Profile
        $employeeC1004 = $this->createEmployee(
            $companyMAC,
            $account1002User04,
            $companyMACHrDepartment,
            DepartmentEmployeeAssignmentType::HEAD,
            $companyMACHrManager,
            null,
            null,
            'C1004', 'José María', 'E', 'Fernández-López');
        $this->createEmploymentProfile($employeeC1004, EmploymentType::CONTRACT);

        //Create Inactive Employee
        $this->createEmployee(
            $companyMAC,
            null,
            $companyMACTrainingAndDevelopmentDepartment,
            DepartmentEmployeeAssignmentType::DEFAULT,
            null,
            null,
            null,
            'C1004-1', 'Ángel', 'J', 'Niño-Ramírez');

        //Create Employee C1005 Info, Contact, Employment Profile
        $employeeC1005 = $this->createEmployee(
            $companyMAC,
            $account1002User05,
            $companyMACPayrollDepartment,
            DepartmentEmployeeAssignmentType::HEAD,
            $companyMACPayrollManager,
            $employeeC1004,
            null,
            'C1005', 'Jürgen', 'C', 'Müller');
        $this->createEmploymentProfile($employeeC1005, EmploymentType::CONTRACT);

        //Create Employee C1006 Info, Contact, Employment Profile
        $employeeC1006 = $this->createEmployee(
            $companyMAC,
            null,
            $companyMACPayrollDepartment,
            DepartmentEmployeeAssignmentType::DEFAULT,
            $companyMACPayrollAssistant,
            null,
            null,
            'C1006', 'Renée', 'S', 'O’Connor');
        $this->createEmploymentProfile($employeeC1006, EmploymentType::OJT);

        //Create Employee C1007 Info, Contact, Employment Profile
        $employeeC1007 = $this->createEmployee(
            $companyMAC,
            null,
            $companyMACPayrollDepartment,
            DepartmentEmployeeAssignmentType::DEFAULT,
            $companyMACPayrollAssistant,
            null,
            null,
            'C1007', 'Søren', 'M', 'Bjørnsen');
        $this->createEmploymentProfile($employeeC1007, EmploymentType::PART_TIME);

        //Create Employee C1008 Info, Contact, Employment Profile
        $employeeC1008 = $this->createEmployee(
            $companyMAC,
            null,
            $companyMACTrainingAndDevelopmentDepartment,
            DepartmentEmployeeAssignmentType::HEAD,
            $companyMACHrAssistant,
            null,
            null,
            'C1008', 'Björk', 'A', 'Guðmundsdóttir');
        $this->createEmploymentProfile($employeeC1008, EmploymentType::PART_TIME);

        //Create Employee C1009 Info, Contact, Employment Profile
        $employeeC1009 = $this->createEmployee(
            $companyMAC,
            null,
            $companyMACInternalAuditDepartment,
            DepartmentEmployeeAssignmentType::DEFAULT,
            $companyMACAccountManager,
            null,
            null,
            'C1009', 'Márton', 'F', 'Szőke');
        $this->createEmploymentProfile($employeeC1009, EmploymentType::FULL_TIME);

        //Create Employee C1010 Info, Contact, Employment Profile
        $employeeC1010 = $this->createEmployee(
            $companyMAC,
            null,
            $companyMACAccountsPayableDepartment,
            DepartmentEmployeeAssignmentType::DEFAULT,
            $companyMACAccountManager,
            null,
            null,
            'C1010', 'İbrahiM', 'I', 'Özdemir');
        $this->createEmploymentProfile($employeeC1010, EmploymentType::FULL_TIME);

        /**************************************************************************************************************************************************************************************************************/

        //Company VTC Compensations
        $companyVTCBasicSalary = $companyVTC->compensations->where('code', 'BASICPAY')->where('type', Compensation::BASIC_PAY)->first();
        $companyVTCMealAllowance = $companyVTC->compensations->where('code', 'MEAL')->where('type', Compensation::REGULAR_ALLOWANCE)->first();
        $companyVTCOvertime = $companyVTC->compensations->where('code', 'OVERTIME')->where('type', Compensation::OVERTIME)->first();

        //Company VTC Deductions
        $companyVTCTardiness = $companyVTC->deductions->where('code', 'TARDINESS')->where('type', Deduction::DEDUCTION)->first();
        $companyVTCUndertime = $companyVTC->deductions->where('code', 'UNDERTIME')->where('type', Deduction::DEDUCTION)->first();
        $companyVTCAbsent = $companyVTC->deductions->where('code', 'ABSENCE')->where('type', Deduction::DEDUCTION)->first();
        $companyVTCSSSEmployed = $companyVTC->deductions->where('code', 'SSS-EMPLOYED')->where('type', Deduction::STATUTORY_CONTRIBUTION)->first();

        //Company VTC Income Taxes
        $companyVTCCompensationTax = $companyVTC->incomeTaxes->where('code', 'WTC ')->where('type', IncomeTax::WITHHOLDING_TAX)->first();

        //Create Compensations for Employee B1001
        $employeeB1001->payrollComponents()->firstOrCreate(
            ['formulable_type' => Formulable::EARNINGS , 'payroll_componentable_id' => $companyVTCBasicSalary->id, 'payroll_componentable_type' => 'compensation'],
            ['formulable_type' => Formulable::EARNINGS , 'payroll_componentable_id' => $companyVTCBasicSalary->id, 'payroll_componentable_type' => 'compensation', 'amount' => '1200.14','currency' => 'PHP', 'pay_period' => PayPeriod::MONTHLY, 'pay_type' => PayType::BY_ATTENDANCE]
        );
        $employeeB1001->payrollComponents()->firstOrCreate(
            ['formulable_type' => Formulable::EARNINGS , 'payroll_componentable_id' => $companyVTCMealAllowance->id, 'payroll_componentable_type' => 'compensation'],
            ['formulable_type' => Formulable::EARNINGS , 'payroll_componentable_id' => $companyVTCMealAllowance->id, 'payroll_componentable_type' => 'compensation', 'amount' => '200', 'currency' => 'PHP', 'pay_period' => PayPeriod::MONTHLY, 'pay_type' => PayType::BY_ATTENDANCE]
        );
        $employeeB1001->payrollComponents()->firstOrCreate(
            ['formulable_type' => Formulable::EARNINGS , 'payroll_componentable_id' => $companyVTCOvertime->id, 'payroll_componentable_type' => 'compensation'],
            ['formulable_type' => Formulable::EARNINGS , 'payroll_componentable_id' => $companyVTCOvertime->id, 'payroll_componentable_type' => 'compensation']
        );
        //Create Deductions for Employee B1001
        $employeeB1001->payrollComponents()->firstOrCreate(['formulable_type' => Formulable::DEDUCTIONS , 'payroll_componentable_id' => $companyVTCSSSEmployed->id, 'payroll_componentable_type' => 'deduction']);
        //Create Income Tax for Employee B1001
        $employeeB1001->payrollComponents()->firstOrCreate(['formulable_type' => Formulable::INCOME_TAX , 'payroll_componentable_id' => $companyVTCCompensationTax->id, 'payroll_componentable_type' => 'income_tax']);

        //Company MAC Compensations
        $companyMACBasicSalary = $companyMAC->compensations->where('code', 'BASICPAY')->where('type', Compensation::BASIC_PAY)->first();
        $companyMACMealAllowance = $companyMAC->compensations->where('code', 'MEAL')->where('type', Compensation::REGULAR_ALLOWANCE)->first();
        $companyMACOvertime = $companyMAC->compensations->where('code', 'OVERTIME')->where('type', Compensation::OVERTIME)->first();

        //Company MAC Deductions
        $companyMACSSSEmployed = $companyMAC->deductions->where('code', 'SSS-EMPLOYED')->where('type', Deduction::STATUTORY_CONTRIBUTION)->first();
        $companyMACPhilhealth = $companyMAC->deductions->where('code', 'PHILHEALTH')->where('type', Deduction::STATUTORY_CONTRIBUTION)->first();
        $companyMACPagIBIG = $companyMAC->deductions->where('code', 'PAG-IBIG')->where('type', Deduction::STATUTORY_CONTRIBUTION)->first();

        //Company MAC Income Taxes
        $companyMACCompensationTax = $companyMAC->incomeTaxes->where('code', 'WTC ')->where('type', IncomeTax::WITHHOLDING_TAX)->first();

        //Create Compensations for Employee C1001
        $employeeC1001->payrollComponents()->firstOrCreate(
            ['formulable_type' => Formulable::EARNINGS , 'payroll_componentable_id' => $companyMACBasicSalary->id, 'payroll_componentable_type' => 'compensation'],
            ['formulable_type' => Formulable::EARNINGS , 'payroll_componentable_id' => $companyMACBasicSalary->id, 'payroll_componentable_type' => 'compensation', 'amount' => '1200.14', 'currency' => 'PHP', 'pay_period' => PayPeriod::MONTHLY, 'pay_type' => PayType::BY_ATTENDANCE]
        );
        $employeeC1001->payrollComponents()->firstOrCreate(
            ['formulable_type' => Formulable::EARNINGS , 'payroll_componentable_id' => $companyMACMealAllowance->id, 'payroll_componentable_type' => 'compensation'],
            ['formulable_type' => Formulable::EARNINGS , 'payroll_componentable_id' => $companyMACMealAllowance->id, 'payroll_componentable_type' => 'compensation', 'amount' => '200', 'currency' => 'PHP', 'pay_period' => PayPeriod::MONTHLY, 'pay_type' => PayType::BY_ATTENDANCE]
        );
        $employeeC1001->payrollComponents()->firstOrCreate(
            ['formulable_type' => Formulable::EARNINGS , 'payroll_componentable_id' => $companyMACOvertime->id, 'payroll_componentable_type' => 'compensation'],
            ['formulable_type' => Formulable::EARNINGS , 'payroll_componentable_id' => $companyMACOvertime->id, 'payroll_componentable_type' => 'compensation']
        );
        //Create Deductions for Employee C1001
        $employeeC1001->payrollComponents()->firstOrCreate(['formulable_type' => Formulable::DEDUCTIONS , 'payroll_componentable_id' => $companyMACSSSEmployed->id, 'payroll_componentable_type' => 'deduction']);
        $employeeC1001->payrollComponents()->firstOrCreate(['formulable_type' => Formulable::DEDUCTIONS , 'payroll_componentable_id' => $companyMACPhilhealth->id, 'payroll_componentable_type' => 'deduction']);
        $employeeC1001->payrollComponents()->firstOrCreate(['formulable_type' => Formulable::DEDUCTIONS , 'payroll_componentable_id' => $companyMACPagIBIG->id, 'payroll_componentable_type' => 'deduction']);
        //Create Income Tax for Employee C1001
        $employeeC1001->payrollComponents()->firstOrCreate(['formulable_type' => Formulable::INCOME_TAX , 'payroll_componentable_id' => $companyMACCompensationTax->id, 'payroll_componentable_type' => 'income_tax']);

        //Create Compensations for Employee C1002
        $employeeC1002->payrollComponents()->firstOrCreate(
            ['formulable_type' => Formulable::EARNINGS , 'payroll_componentable_id' => $companyMACBasicSalary->id, 'payroll_componentable_type' => 'compensation'],
            ['formulable_type' => Formulable::EARNINGS , 'payroll_componentable_id' => $companyMACBasicSalary->id, 'payroll_componentable_type' => 'compensation', 'amount' => '1100', 'currency' => 'PHP', 'pay_period' => PayPeriod::DAILY, 'pay_type' => PayType::BY_ATTENDANCE]
        );
        $employeeC1002->payrollComponents()->firstOrCreate(
            ['formulable_type' => Formulable::EARNINGS , 'payroll_componentable_id' => $companyMACMealAllowance->id, 'payroll_componentable_type' => 'compensation'],
            ['formulable_type' => Formulable::EARNINGS , 'payroll_componentable_id' => $companyMACMealAllowance->id, 'payroll_componentable_type' => 'compensation', 'amount' => '1100', 'currency' => 'PHP', 'pay_period' => PayPeriod::DAILY, 'pay_type' => PayType::BY_ATTENDANCE]
        );
        $employeeC1002->payrollComponents()->firstOrCreate(
            ['formulable_type' => Formulable::EARNINGS , 'payroll_componentable_id' => $companyMACOvertime->id, 'payroll_componentable_type' => 'compensation'],
            ['formulable_type' => Formulable::EARNINGS , 'payroll_componentable_id' => $companyMACOvertime->id, 'payroll_componentable_type' => 'compensation']
        );
        //Create Deductions for Employee C1002
        $employeeC1002->payrollComponents()->firstOrCreate(['formulable_type' => Formulable::DEDUCTIONS , 'payroll_componentable_id' => $companyMACSSSEmployed->id, 'payroll_componentable_type' => 'deduction']);
        $employeeC1002->payrollComponents()->firstOrCreate(['formulable_type' => Formulable::DEDUCTIONS , 'payroll_componentable_id' => $companyMACPhilhealth->id, 'payroll_componentable_type' => 'deduction']);
        $employeeC1002->payrollComponents()->firstOrCreate(['formulable_type' => Formulable::DEDUCTIONS , 'payroll_componentable_id' => $companyMACPagIBIG->id, 'payroll_componentable_type' => 'deduction']);
        //Create Income Tax for Employee C1002
        $employeeC1002->payrollComponents()->firstOrCreate(['formulable_type' => Formulable::INCOME_TAX , 'payroll_componentable_id' => $companyMACCompensationTax->id, 'payroll_componentable_type' => 'income_tax']);

        //Create Compensations for Employee C1003
        $employeeC1003->payrollComponents()->firstOrCreate(
            ['formulable_type' => Formulable::EARNINGS , 'payroll_componentable_id' => $companyMACBasicSalary->id, 'payroll_componentable_type' => 'compensation'],
            ['formulable_type' => Formulable::EARNINGS , 'payroll_componentable_id' => $companyMACBasicSalary->id, 'payroll_componentable_type' => 'compensation', 'amount' => '420', 'currency' => 'PHP', 'pay_period' => PayPeriod::MONTHLY, 'pay_type' => PayType::BY_ATTENDANCE]
        );
        $employeeC1003->payrollComponents()->firstOrCreate(
            ['formulable_type' => Formulable::EARNINGS , 'payroll_componentable_id' => $companyMACMealAllowance->id, 'payroll_componentable_type' => 'compensation'],
            ['formulable_type' => Formulable::EARNINGS , 'payroll_componentable_id' => $companyMACMealAllowance->id, 'payroll_componentable_type' => 'compensation', 'amount' => '20', 'currency' => 'PHP', 'pay_period' => PayPeriod::MONTHLY, 'pay_type' => PayType::BY_ATTENDANCE]
        );
        $employeeC1003->payrollComponents()->firstOrCreate(
            ['formulable_type' => Formulable::EARNINGS , 'payroll_componentable_id' => $companyMACOvertime->id, 'payroll_componentable_type' => 'compensation'],
            ['formulable_type' => Formulable::EARNINGS , 'payroll_componentable_id' => $companyMACOvertime->id, 'payroll_componentable_type' => 'compensation']
        );
        //Create Deductions for Employee C1003
        $employeeC1003->payrollComponents()->firstOrCreate(['formulable_type' => Formulable::DEDUCTIONS , 'payroll_componentable_id' => $companyMACSSSEmployed->id, 'payroll_componentable_type' => 'deduction']);
        //Create Income Tax for Employee C1003
        $employeeC1003->payrollComponents()->firstOrCreate(['formulable_type' => Formulable::INCOME_TAX , 'payroll_componentable_id' => $companyMACCompensationTax->id, 'payroll_componentable_type' => 'income_tax']);

        /**************************************************************************************************************************************************************************************************************/

        //Company MAC Holidays
        $companyMAC->holidays()->firstOrCreate([
            'name' => 'Legal Recurring Jan 11th',
        ],[
            'ulid' => Str::ulid(),
            'name' => 'Legal Recurring Jan 11th',
            'type' => HolidayType::LEGAL,
            'date' => '2000-01-11',
            'recurring' => true,
            'active' => true,
            'effective_date' => '2000-01-11',
        ]);
        $companyMAC->holidays()->firstOrCreate([
            'name' => 'Special Recurring Jan 13th',
        ],[
            'ulid' => Str::ulid(),
            'name' => 'Special Recurring Jan 13th',
            'type' => HolidayType::SPECIAL,
            'date' => '2000-01-13',
            'recurring' => true,
            'active' => true,
            'effective_date' => '2000-01-11',
        ]);

        $companyMAC->holidays()->firstOrCreate([
            'name' => 'Feb 2nd Non-paid Special holiday',
        ],[
            'ulid' => Str::ulid(),
            'name' => 'Feb 2nd Non-paid Special holiday',
            'type' => HolidayType::SPECIAL,
            'date' => '2000-02-02',
            'recurring' => true,
            'active' => true,
            'effective_date' => '2000-02-02',
        ]);

        $companyMAC->holidays()->firstOrCreate([
            'name' => 'Feb 3rd Non-paid Special holiday',
        ],[
            'ulid' => Str::ulid(),
            'name' => 'Feb 3rd Non-paid Special holiday',
            'type' => HolidayType::SPECIAL,
            'date' => '2000-02-03',
            'recurring' => true,
            'active' => true,
            'effective_date' => '2000-02-03',
        ]);

        $companyMAC->holidays()->firstOrCreate([
            'name' => 'Feb 5th Paid Legal holiday',
        ],[
            'ulid' => Str::ulid(),
            'name' => 'Feb 5th Paid Legal holiday',
            'type' => HolidayType::LEGAL,
            'date' => '2000-02-05',
            'recurring' => true,
            'active' => true,
            'effective_date' => '2000-02-05',
        ]);

        $companyMAC->holidays()->firstOrCreate([
            'name' => 'Feb 6th Paid Legal holiday',
        ],[
            'ulid' => Str::ulid(),
            'name' => 'Feb 6th Paid Legal holiday',
            'type' => HolidayType::LEGAL,
            'date' => '2000-02-06',
            'recurring' => true,
            'active' => true,
            'effective_date' => '2000-02-06',
        ]);

        $companyMAC->holidays()->firstOrCreate([
            'name' => 'Feb 7th Paid Legal holiday',
        ],[
            'ulid' => Str::ulid(),
            'name' => 'Feb 7th Legal holiday',
            'type' => HolidayType::LEGAL,
            'date' => '2000-02-07',
            'recurring' => true,
            'active' => true,
            'effective_date' => '2000-02-07',
        ]);

        $companyMAC->holidays()->firstOrCreate([
            'name' => 'Feb 9th Paid Legal holiday',
        ],[
            'ulid' => Str::ulid(),
            'name' => 'Feb 9th Legal holiday',
            'type' => HolidayType::LEGAL,
            'date' => '2000-02-09',
            'recurring' => true,
            'active' => true,
            'effective_date' => '2000-02-09',
        ]);

        $companyMAC->holidays()->firstOrCreate([
            'name' => 'Feb 10th Paid Legal holiday',
        ],[
            'ulid' => Str::ulid(),
            'name' => 'Feb 10th Legal holiday',
            'type' => HolidayType::LEGAL,
            'date' => '2000-02-10',
            'recurring' => true,
            'active' => true,
            'effective_date' => '2000-02-10',
        ]);

        $companyMAC->holidays()->firstOrCreate([
            'name' => 'Feb 11th Non-paid Special holiday',
        ],[
            'ulid' => Str::ulid(),
            'name' => 'Feb 11th Non-paid Special holiday',
            'type' => HolidayType::SPECIAL,
            'date' => '2000-02-11',
            'recurring' => true,
            'active' => true,
            'effective_date' => '2000-02-11',
        ]);

        $companyMAC->holidays()->firstOrCreate([
            'name' => 'Feb 12th Non-paid Special holiday',
        ],[
            'ulid' => Str::ulid(),
            'name' => 'Feb 12th Non-paid Special holiday',
            'type' => HolidayType::SPECIAL,
            'date' => '2000-02-12',
            'recurring' => true,
            'active' => true,
            'effective_date' => '2000-02-12',
        ]);

        $companyMAC->holidays()->firstOrCreate([
            'name' => 'Feb 14th Paid Double holiday',
        ],[
            'ulid' => Str::ulid(),
            'name' => 'Feb 14th Paid Double holiday',
            'type' => HolidayType::DOUBLE,
            'date' => '2000-02-14',
            'recurring' => true,
            'active' => true,
            'effective_date' => '2000-02-14',
        ]);

        $companyMAC->holidays()->firstOrCreate([
            'name' => 'Feb 16th  Non-paid Special holiday',
        ],[
            'ulid' => Str::ulid(),
            'name' => 'Feb 16th  Non-paid Special holiday',
            'type' => HolidayType::SPECIAL,
            'date' => '2000-02-16',
            'recurring' => true,
            'active' => true,
            'effective_date' => '2000-02-16',
        ]);

        $companyMAC->holidays()->firstOrCreate([
            'name' => 'Feb 17th Paid Legal holiday',
        ],[
            'ulid' => Str::ulid(),
            'name' => 'Feb 17th Paid Legal holiday',
            'type' => HolidayType::LEGAL,
            'date' => '2000-02-17',
            'recurring' => true,
            'active' => true,
            'effective_date' => '2000-02-17',
        ]);

        $companyMAC->holidays()->firstOrCreate([
            'name' => 'Feb 18th Non-paid Special holiday',
        ],[
            'ulid' => Str::ulid(),
            'name' => 'Feb 18th Non-paid Special holiday',
            'type' => HolidayType::SPECIAL,
            'date' => '2000-02-18',
            'recurring' => true,
            'active' => true,
            'effective_date' => '2000-02-18',
        ]);

        $companyMAC->holidays()->firstOrCreate([
            'name' => 'Feb 19th Paid Double holiday',
        ],[
            'ulid' => Str::ulid(),
            'name' => 'Feb 19th Paid Double holiday',
            'type' => HolidayType::DOUBLE,
            'date' => '2000-02-19',
            'recurring' => true,
            'active' => true,
            'effective_date' => '2000-02-19',
        ]);

        $companyMAC->holidays()->firstOrCreate([
            'name' => 'Feb 20th Paid Double holiday',
        ],[
            'ulid' => Str::ulid(),
            'name' => 'Feb 20th Paid Double holiday',
            'type' => HolidayType::DOUBLE,
            'date' => '2000-02-20',
            'recurring' => true,
            'active' => true,
            'effective_date' => '2000-02-20',
        ]);

        $companyMAC->holidays()->firstOrCreate([
            'name' => 'Feb 21st Non-paid Special holiday',
        ],[
            'ulid' => Str::ulid(),
            'name' => 'Feb 21st Non-paid Special holiday',
            'type' => HolidayType::SPECIAL,
            'date' => '2000-02-21',
            'recurring' => true,
            'active' => true,
            'effective_date' => '2000-02-21',
        ]);

        $companyMAC->holidays()->firstOrCreate([
            'name' => 'Feb 24th Paid Double holiday',
        ],[
            'ulid' => Str::ulid(),
            'name' => 'Feb 24th Paid Double holiday',
            'type' => HolidayType::DOUBLE,
            'date' => '2000-02-24',
            'recurring' => true,
            'active' => true,
            'effective_date' => '2000-02-24',
        ]);

        $companyMAC->holidays()->firstOrCreate([
            'name' => 'Feb 25th Paid Double holiday',
        ],[
            'ulid' => Str::ulid(),
            'name' => 'Feb 25th Paid Double holiday',
            'type' => HolidayType::DOUBLE,
            'date' => '2000-02-25',
            'recurring' => true,
            'active' => true,
            'effective_date' => '2000-02-25',
        ]);

        $companyMAC->holidays()->firstOrCreate(['name' => '2026-02-26 Legal',],['ulid' => Str::ulid(), 'name' => '2026-02-26 Legal', 'type' => HolidayType::LEGAL, 'date' => '2026-02-26', 'recurring' => false, 'active' => true, 'effective_date' => '2000-01-01',]);
        $companyMAC->holidays()->firstOrCreate(['name' => '2026-02-27 Legal',],['ulid' => Str::ulid(), 'name' => '2026-02-27 Legal', 'type' => HolidayType::LEGAL, 'date' => '2026-02-27', 'recurring' => false, 'active' => true, 'effective_date' => '2000-01-01',]);
        $companyMAC->holidays()->firstOrCreate(['name' => '2026-03-04 Legal',],['ulid' => Str::ulid(), 'name' => '2026-03-04 Legal', 'type' => HolidayType::LEGAL, 'date' => '2026-03-04', 'recurring' => false, 'active' => true, 'effective_date' => '2000-01-01',]);
        $companyMAC->holidays()->firstOrCreate(['name' => '2026-03-05 Legal',],['ulid' => Str::ulid(), 'name' => '2026-03-05 Legal', 'type' => HolidayType::LEGAL, 'date' => '2026-03-05', 'recurring' => false, 'active' => true, 'effective_date' => '2000-01-01',]);
        $companyMAC->holidays()->firstOrCreate(['name' => '2026-03-07 Special',],['ulid' => Str::ulid(), 'name' => '2026-03-07 Special', 'type' => HolidayType::SPECIAL, 'date' => '2026-03-07', 'recurring' => false, 'active' => true, 'effective_date' => '2000-01-01',]);
        $companyMAC->holidays()->firstOrCreate(['name' => '2026-03-09 Legal',],['ulid' => Str::ulid(), 'name' => '2026-03-09 Legal', 'type' => HolidayType::LEGAL, 'date' => '2026-03-09', 'recurring' => false, 'active' => true, 'effective_date' => '2000-01-01',]);
        $companyMAC->holidays()->firstOrCreate(['name' => '2026-03-11 Special',],['ulid' => Str::ulid(), 'name' => '2026-03-11 Special', 'type' => HolidayType::SPECIAL, 'date' => '2026-03-11', 'recurring' => false, 'active' => true, 'effective_date' => '2000-01-01',]);
        $companyMAC->holidays()->firstOrCreate(['name' => '2026-03-12 Legal',],['ulid' => Str::ulid(), 'name' => '2026-03-12 Legal', 'type' => HolidayType::LEGAL, 'date' => '2026-03-12', 'recurring' => false, 'active' => true, 'effective_date' => '2000-01-01',]);
        $companyMAC->holidays()->firstOrCreate(['name' => '2026-03-13 Special',],['ulid' => Str::ulid(), 'name' => '2026-03-13 Special', 'type' => HolidayType::SPECIAL, 'date' => '2026-03-13', 'recurring' => false, 'active' => true, 'effective_date' => '2000-01-01',]);
        $companyMAC->holidays()->firstOrCreate(['name' => '2026-03-14 Legal',],['ulid' => Str::ulid(), 'name' => '2026-03-14 Legal', 'type' => HolidayType::LEGAL, 'date' => '2026-03-14', 'recurring' => false, 'active' => true, 'effective_date' => '2000-01-01',]);
        $companyMAC->holidays()->firstOrCreate(['name' => '2026-03-16 Legal',],['ulid' => Str::ulid(), 'name' => '2026-03-16 Legal', 'type' => HolidayType::LEGAL, 'date' => '2026-03-16', 'recurring' => false, 'active' => true, 'effective_date' => '2000-01-01',]);
        $companyMAC->holidays()->firstOrCreate(['name' => '2026-03-17 Legal',],['ulid' => Str::ulid(), 'name' => '2026-03-17 Legal', 'type' => HolidayType::LEGAL, 'date' => '2026-03-17', 'recurring' => false, 'active' => true, 'effective_date' => '2000-01-01',]);
        $companyMAC->holidays()->firstOrCreate(['name' => '2026-03-18 Double',],['ulid' => Str::ulid(), 'name' => '2026-03-18 Double', 'type' => HolidayType::DOUBLE, 'date' => '2026-03-18', 'recurring' => false, 'active' => true, 'effective_date' => '2000-01-01',]);
        $companyMAC->holidays()->firstOrCreate(['name' => '2026-03-20 Legal',],['ulid' => Str::ulid(), 'name' => '2026-03-20 Legal', 'type' => HolidayType::LEGAL, 'date' => '2026-03-20', 'recurring' => false, 'active' => true, 'effective_date' => '2000-01-01',]);
        $companyMAC->holidays()->firstOrCreate(['name' => '2026-03-21 Double',],['ulid' => Str::ulid(), 'name' => '2026-03-21 Double', 'type' => HolidayType::DOUBLE, 'date' => '2026-03-21', 'recurring' => false, 'active' => true, 'effective_date' => '2000-01-01',]);
        $companyMAC->holidays()->firstOrCreate(['name' => '2026-03-24 Legal',],['ulid' => Str::ulid(), 'name' => '2026-03-24 Legal', 'type' => HolidayType::LEGAL, 'date' => '2026-03-24', 'recurring' => false, 'active' => true, 'effective_date' => '2000-01-01',]);
        $companyMAC->holidays()->firstOrCreate(['name' => '2026-03-25 Double',],['ulid' => Str::ulid(), 'name' => '2026-03-25 Double', 'type' => HolidayType::DOUBLE, 'date' => '2026-03-25', 'recurring' => false, 'active' => true, 'effective_date' => '2000-01-01',]);
    }

    public function createPayrollComponent(Model $company, $index, $formulableType, $component, $attributes): void
    {
        $formulas = $company->formulas;

        $companyFormula = $formulas->where('formulable_type', $formulableType)
            ->where('component_type', $attributes['type'])
            ->where('name', $attributes['formula'])
            ->first()
            ?->pivot;

        if(empty($companyFormula)) return;

        $company->{$component}()->firstOrCreate([
            'code' => $attributes['code'],
        ],[
            ...collect($attributes)->except('formula')->toArray(),
            'order' => ++$index,
            'company_formula_id' => $companyFormula->id,
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

                $shift->schedules()->firstOrCreate([
                    'week_day' => $weekday
                ],[
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

                $shift->schedules()->firstOrCreate([
                    'week_day' => $weekday
                ],[
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
        ?DepartmentEmployeeAssignmentType $departmentAssignmentType = null,
        ?Designation $designation = null,
        ?Employee $manager = null,
        ?PayFrequency $payFrequency = null,
        $number = null,
        $givenName = null,
        $middleName = null,
        $familyName = null,
    ){
        $baseModel = empty($user) ? $company : $user;

        $employee = $baseModel->employees()->firstOrCreate([
            'company_id' => $company->id,
            'number' => $number,
        ],[
            'ulid' => Str::ulid(),
            'company_id' => $company->id,
            'designation_id' => $designation?->id,
            'manager_id' => $manager?->id,
            'pay_frequency_id' => $payFrequency?->id,
            'number' => $number,
            'given_name' => $givenName,
            'middle_name' => $middleName,
            'family_name' => $familyName,
        ]);

        if(!empty($department) && !empty($departmentAssignmentType)){
            $employee->departments()->sync([$department->id => ['department_assignment_type' => $departmentAssignmentType->value]]);
        }

        return $employee;
    }

    public function createEmployeeContact(Employee $employee, $officeEmail = null, $personalEmail = null, $officePhone = null, $personalPhone = null)
    {
        return !empty($employee->contact)
            ? $employee->contact
            : $employee->contact()->create([
                'office_email' => $officeEmail,
                'personal_email' => $personalEmail,
                'office_phone' => $officePhone,
                'personal_phone' => $personalPhone
            ]);
    }

    public function createEmploymentProfile(Employee $employee, EmploymentType $employmentType = EmploymentType::NOT_SPECIFIED)
    {
        return !$employee->employmentProfiles->isEmpty()
            ? $employee->employmentProfiles->first()
            : $employee->employmentProfiles()->create([
                'status' => EmploymentStatus::ACTIVE,
                'employment_type' => $employmentType,
                'start_date' => Carbon::now()->toDateString()
            ]);
    }
}
