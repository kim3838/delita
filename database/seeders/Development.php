<?php

namespace Database\Seeders;

use App\Enums\CompanyUserAssignmentType;
use App\Enums\Compensation;
use App\Enums\Deduction;
use App\Enums\Formulable;
use App\Enums\Gender;
use App\Enums\IncomeTax;
use App\Enums\MaritalStatus;
use App\Enums\PayFrequency;
use App\Enums\PayPeriod;
use App\Enums\PayType;
use App\Enums\TimePeriodType;
use App\Models\Account;
use App\Models\Employee;
use App\Models\Formula;
use App\Models\Prototype;
use App\Models\TimePeriodPreset;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class Development extends Seeder
{
    use WithoutModelEvents;

    public static $salaryStatementModules = [
        [
            'formulable_type' => Formulable::EARNINGS,
            'order' => 1,
            'name' => 'Assigned Compensations',
            'reference' => 'employee_compensation',
            'conditions' => [
                [
                    'subject' => 'assignable',
                    'operator' => '=',
                    'value' => '1',
                ]
            ]
        ],[
            'formulable_type' => Formulable::DEDUCTIONS,
            'order' => 2,
            'name' => 'Assigned Deductions',
            'reference' => 'employee_deduction',
            'conditions' => [
                [
                    'subject' => 'assignable',
                    'operator' => '=',
                    'value' => '1',
                ]
            ]
        ],[
            'formulable_type' => Formulable::TAXABLE_INCOME,
            'order' => 3,
            'name' => 'Taxable Income',
            'reference' => null,
            'conditions' => null
        ],[
            'formulable_type' => Formulable::NONTAXABLE_INCOME,
            'order' => 4,
            'name' => 'Non-Taxable Income',
            'reference' => null,
            'conditions' => null
        ],[
            'formulable_type' => Formulable::INCOME_TAX,
            'order' => 5,
            'name' => 'Assigned Income Taxes',
            'reference' => 'employee_income_tax',
            'conditions' => [
                [
                    'subject' => 'assignable',
                    'operator' => '=',
                    'value' => '1',
                ]
            ]
        ],[
            'formulable_type' => Formulable::NET_INCOME,
            'order' => 6,
            'name' => 'Net Income',
            'reference' => null,
            'conditions' => null
        ],
    ];

    public static $timePeriodPresets = [
        [
            'type' => TimePeriodType::THIRTEENTH_MONTH,
            'name' => 'november_2nd',
            'readable_name' => 'November 2nd',
            'yearly_period' => [
                [
                    'key' => 'start_date',
                    'label' => 'Start Date',
                    'order' => 1,
                    'type' => 'date',
                    'readable' => 'November 02 of last year',
                    'value' => [
                        'base' => 'Nov 02 last year',
                        'year' => null,
                        'month' => null,
                        'day' => null,
                        'time' => 'startOfDay'
                    ]
                ],[
                    'key' => 'end_date',
                    'label' => 'End Date',
                    'order' => 2,
                    'type' => 'date',
                    'readable' => 'November 01 of current year',
                    'value' => [
                        'base' => 'Nov 01',
                        'year' => null,
                        'month' => null,
                        'day' => null,
                        'time' => 'endOfDay'
                    ]
                ]
            ]
        ], [
            'type' => TimePeriodType::PAY_PERIOD,
            'name' => 'end_of_month_cut_off',
            'readable_name' => 'Cut-off of End of month',
            'monthly_period' => [
                [
                    'key' => 'start_date',
                    'label' => 'Start Date',
                    'order' => 1,
                    'type' => 'date',
                    'readable' => '01 of month',
                    'value' => [
                        'base' => 'now',
                        'year' => null,
                        'month' => null,
                        'day' => 'startOfMonth',
                        'time' => 'startOfDay'
                    ]
                ],[
                    'key' => 'end_date',
                    'label' => 'End Date',
                    'order' => 2,
                    'type' => 'date',
                    'readable' => 'End of month',
                    'value' => [
                        'base' => 'now',
                        'year' => null,
                        'month' => null,
                        'day' => 'endOfMonth',
                        'time' => 'endOfDay'
                    ]
                ]
            ],
            'semimonthly_period' => [
                [
                    'key' => '1st_half_start_date',
                    'label' => '1st Half Start Date',
                    'order' => 1,
                    'type' => 'date',
                    'readable' => '01 of month',
                    'value' => [
                        'base' => 'now',
                        'year' => null,
                        'month' => null,
                        'day' => 'startOfMonth',
                        'time' => 'startOfDay'
                    ]
                ],[
                    'key' => '1st_half_end_date',
                    'label' => '1st Half End Date',
                    'order' => 2,
                    'type' => 'date',
                    'readable' => '15 of month',
                    'value' => [
                        'base' => 'now',
                        'year' => null,
                        'month' => null,
                        'day' => 15,
                        'time' => 'endOfDay'
                    ]
                ],[
                    'key' => '2nd_half_start_date',
                    'label' => '2nd Half Start Date',
                    'order' => 3,
                    'type' => 'date',
                    'readable' => '16 of month',
                    'value' => [
                        'base' => 'now',
                        'year' => null,
                        'month' => null,
                        'day' => 16,
                        'time' => 'startOfDay'
                    ]
                ],[
                    'key' => '2nd_half_end_date',
                    'label' => '2nd Half End Date',
                    'order' => 4,
                    'type' => 'date',
                    'readable' => 'End of month',
                    'value' => [
                        'base' => 'now',
                        'year' => null,
                        'month' => null,
                        'day' => 'endOfMonth',
                        'time' => 'endOfDay'
                    ]
                ],
            ]
        ], [
            'type' => TimePeriodType::PAY_PERIOD,
            'name' => '10th_cut_off',
            'readable_name' => 'Cut-off of 10th',
            'monthly_period' => [
                [
                    'key' => 'start_date',
                    'label' => 'Start Date',
                    'order' => 1,
                    'type' => 'date',
                    'readable' => '11 of current month',
                    'value' => [
                        'base' => 'now',
                        'year' => null,
                        'month' => null,
                        'day' => 11,
                        'time' => 'startOfDay'
                    ]
                ],[
                    'key' => 'end_date',
                    'label' => 'End Date',
                    'order' => 2,
                    'type' => 'date',
                    'readable' => '10 of next month',
                    'value' => [
                        'base' => 'now',
                        'year' => null,
                        'month' => 'addMonth.1',
                        'day' => 10,
                        'time' => 'endOfDay'
                    ]
                ]
            ],
            'semimonthly_period' => [
                [
                    'key' => '1st_half_start_date',
                    'label' => '1st Half Start Date',
                    'order' => 1,
                    'type' => 'date',
                    'readable' => '11 of current month',
                    'value' => [
                        'base' => 'now',
                        'year' => null,
                        'month' => null,
                        'day' => 11,
                        'time' => 'startOfDay'
                    ]
                ],[
                    'key' => '1st_half_end_date',
                    'label' => '1st Half End Date',
                    'order' => 2,
                    'type' => 'date',
                    'readable' => '25 of current month',
                    'value' => [
                        'base' => 'now',
                        'year' => null,
                        'month' => null,
                        'day' => 25,
                        'time' => 'endOfDay'
                    ]
                ],[
                    'key' => '2nd_half_start_date',
                    'label' => '2nd Half Start Date',
                    'order' => 3,
                    'type' => 'date',
                    'readable' => '26 of current month',
                    'value' => [
                        'base' => 'now',
                        'year' => null,
                        'month' => null,
                        'day' => 26,
                        'time' => 'startOfDay'
                    ]
                ],[
                    'key' => '2nd_half_end_date',
                    'label' => '2nd Half End Date',
                    'order' => 4,
                    'type' => 'date',
                    'readable' => '10 of next month',
                    'value' => [
                        'base' => 'now',
                        'year' => null,
                        'month' => 'addMonth.1',
                        'day' => 10,
                        'time' => 'endOfDay'
                    ]
                ],
            ]
        ], [
            'type' => TimePeriodType::PAY_PERIOD,
            'name' => '25th_cut_off',
            'readable_name' => 'Cut-off of 25th',
            'monthly_period' => [
                [
                    'key' => 'start_date',
                    'label' => 'Start Date',
                    'order' => 1,
                    'type' => 'date',
                    'readable' => '26 of last month',
                    'value' => [
                        'base' => 'now',
                        'year' => null,
                        'month' => 'subMonth.1',
                        'day' => 26,
                        'time' => 'startOfDay'
                    ]
                ],[
                    'key' => 'end_date',
                    'label' => 'End Date',
                    'order' => 2,
                    'type' => 'date',
                    'readable' => '25 of current month',
                    'value' => [
                        'base' => 'now',
                        'year' => null,
                        'month' => null,
                        'day' => 25,
                        'time' => 'endOfDay'
                    ]
                ]
            ],
            'semimonthly_period' => [
                [
                    'key' => '1st_half_start_date',
                    'label' => '1st Half Start Date',
                    'order' => 1,
                    'type' => 'date',
                    'readable' => '26 of last month',
                    'value' => [
                        'base' => 'now',
                        'year' => null,
                        'month' => 'subMonth.1',
                        'day' => 26,
                        'time' => 'startOfDay'
                    ]
                ],[
                    'key' => '1st_half_end_date',
                    'label' => '1st Half End Date',
                    'order' => 2,
                    'type' => 'date',
                    'readable' => '10 of current month',
                    'value' => [
                        'base' => 'now',
                        'year' => null,
                        'month' => null,
                        'day' => 10,
                        'time' => 'endOfDay'
                    ]
                ],[
                    'key' => '2nd_half_start_date',
                    'label' => '2nd Half Start Date',
                    'order' => 3,
                    'type' => 'date',
                    'readable' => '11 of current month',
                    'value' => [
                        'base' => 'now',
                        'year' => null,
                        'month' => null,
                        'day' => 11,
                        'time' => 'startOfDay'
                    ]
                ],[
                    'key' => '2nd_half_end_date',
                    'label' => '2nd Half End Date',
                    'order' => 4,
                    'type' => 'date',
                    'readable' => '25 of current month',
                    'value' => [
                        'base' => 'now',
                        'year' => null,
                        'month' => null,
                        'day' => 25,
                        'time' => 'endOfDay'
                    ]
                ],
            ]
        ], [
            'type' => TimePeriodType::NIGHT_DIFFERENTIAL_HOURS,
            'name' => 'night_differential_hours',
            'readable_name' => 'Night Differential Hours',
            'hour_period' => [
                [
                    'key' => 'start_time',
                    'label' => 'Start Time',
                    'order' => 1,
                    'type' => 'date',
                    'readable' => '10 PM',
                    'value' => [
                        'base' => 'now',
                        'year' => null,
                        'month' => null,
                        'day' => null,
                        'time' => [22,0,0]
                    ]
                ],[
                    'key' => 'end_time',
                    'label' => 'End Time',
                    'order' => 2,
                    'type' => 'date',
                    'readable' => '06 AM',
                    'value' => [
                        'base' => 'now',
                        'year' => null,
                        'month' => null,
                        'day' => 'addDay.1',
                        'time' => [6,0,0]
                    ]
                ]
            ]
        ]
    ];

    public function run(): void
    {
        Prototype::factory()->count(500)->create();

        //TimePeriod Presets
        foreach (self::$timePeriodPresets as $timePeriodPreset) {
            TimePeriodPreset::create($timePeriodPreset);
        }

        //Yearly Period Preset of Nov 2nd
        $november2ndThirteenMonthPeriodPreset = collect(self::$timePeriodPresets)
            ->where('name', 'november_2nd')
            ->where('type', TimePeriodType::THIRTEENTH_MONTH)
            ->first();

        $endOfMonthCutOffPeriodPreset = collect(self::$timePeriodPresets)
            ->where('type', TimePeriodType::PAY_PERIOD)
            ->where('name', 'end_of_month_cut_off')
            ->first();

        $twentyFifthCutOffPeriodPreset = collect(self::$timePeriodPresets)
            ->where('type', TimePeriodType::PAY_PERIOD)
            ->where('name', '25th_cut_off')
            ->first();

        $nightDifferentialPeriodPreset = collect(self::$timePeriodPresets)
            ->where('name', 'night_differential_hours')
            ->where('type', TimePeriodType::NIGHT_DIFFERENTIAL_HOURS)
            ->first();

        //13th Month Formula Preset
        $thirteenMonthFormulaPreset = ['name' => 'Standard-13th-Month', 'formulable_type' => Formulable::EARNINGS  ,'component_type' => Compensation::BENEFIT, 'interpolation' => false,
            'default_settings' => $november2ndThirteenMonthPeriodPreset['yearly_period']
        ];

        //Formula Presets
        $formulaPresets = [
            ['name' => 'Standard-Salary', 'formulable_type' => Formulable::EARNINGS ,'component_type' => Compensation::SALARY, 'interpolation' => false,
                'default_settings' => [
                    [
                        'key' => 'regular_rates',
                        'label' => 'Regular Rates',
                        'order' => 1,
                        'type' => 'array',
                        'readable' => 'Regular Rates',
                        'value' => [
                            [
                                'key' => 'regular_day',
                                'label' => 'Regular Day',
                                'order' => 1,
                                'type' => 'number',
                                'readable' => '100%',
                                'value' => '1'
                            ],
                            [
                                'key' => 'rest_day',
                                'label' => 'Rest Day',
                                'order' => 2,
                                'type' => 'number',
                                'readable' => '130%',
                                'value' => '1.3'
                            ],
                            [
                                'key' => 'special_holiday',
                                'label' => 'Special Holiday',
                                'order' => 3,
                                'type' => 'number',
                                'readable' => '130%',
                                'value' => '1.3'
                            ],
                            [
                                'key' => 'special_holiday_and_rest_day',
                                'label' => 'Special Holiday & Rest Day',
                                'order' => 4,
                                'type' => 'number',
                                'readable' => '150%',
                                'value' => '1.5'
                            ],
                            [
                                'key' => 'legal_holiday',
                                'label' => 'Legal Holiday',
                                'order' => 5,
                                'type' => 'number',
                                'readable' => '200%',
                                'value' => '2'
                            ],
                            [
                                'key' => 'legal_holiday_and_rest_day',
                                'label' => 'Legal Holiday & Rest Day',
                                'order' => 6,
                                'type' => 'number',
                                'readable' => '260%',
                                'value' => '2.6'
                            ],
                            [
                                'key' => 'double_holiday',
                                'label' => 'Double Holiday',
                                'order' => 7,
                                'type' => 'number',
                                'readable' => '300%',
                                'value' => '3'
                            ],
                            [
                                'key' => 'double_holiday_and_rest_day',
                                'label' => 'Double Holiday & Rest Day',
                                'order' => 8,
                                'type' => 'number',
                                'readable' => '390%',
                                'value' => '3.9'
                            ],
                        ]
                    ],
                    [
                        'key' => 'night_differential_rates',
                        'label' => 'Night Differential Rates',
                        'order' => 2,
                        'type' => 'array',
                        'readable' => 'Night Differential Rates',
                        'value' => [
                            [
                                'key' => 'regular_day',
                                'label' => 'Regular Day',
                                'order' => 1,
                                'type' => 'number',
                                'readable' => '110%',
                                'value' => '1.10'
                            ],
                            [
                                'key' => 'rest_day',
                                'label' => 'Rest Day',
                                'order' => 2,
                                'type' => 'number',
                                'readable' => '143%',
                                'value' => '1.43'
                            ],
                            [
                                'key' => 'special_holiday',
                                'label' => 'Special Holiday',
                                'order' => 3,
                                'type' => 'number',
                                'readable' => '143%',
                                'value' => '1.43'
                            ],
                            [
                                'key' => 'special_holiday_and_rest_day',
                                'label' => 'Special Holiday & Rest Day',
                                'order' => 4,
                                'type' => 'number',
                                'readable' => '165%',
                                'value' => '1.65'
                            ],
                            [
                                'key' => 'legal_holiday',
                                'label' => 'Legal Holiday',
                                'order' => 5,
                                'type' => 'number',
                                'readable' => '220%',
                                'value' => '2.2'
                            ],
                            [
                                'key' => 'legal_holiday_and_rest_day',
                                'label' => 'Legal Holiday & Rest Day',
                                'order' => 6,
                                'type' => 'number',
                                'readable' => '286%',
                                'value' => '2.86'
                            ],
                            [
                                'key' => 'double_holiday',
                                'label' => 'Double Holiday',
                                'order' => 7,
                                'type' => 'number',
                                'readable' => '330%',
                                'value' => '3.30'
                            ],
                            [
                                'key' => 'double_holiday_and_rest_day',
                                'label' => 'Double Holiday & Rest Day',
                                'order' => 8,
                                'type' => 'number',
                                'readable' => '429%',
                                'value' => '4.29'
                            ],
                        ]
                    ],
                    [
                        'key' => 'night_differential_hours',
                        'label' => 'Night Differential Hours',
                        'order' => 3,
                        'type' => 'array',
                        'readable' => 'Night Differential Hours',
                        'value' => [...$nightDifferentialPeriodPreset['hour_period']]
                    ],
                ]
            ],
            ['name' => 'Standard-Overtime', 'formulable_type' => Formulable::EARNINGS ,'component_type' => Compensation::OVERTIME, 'interpolation' => false,
                'default_settings' => [
                    [
                        'key' => 'regular_rates',
                        'label' => 'Regular Rates',
                        'order' => 1,
                        'type' => 'array',
                        'readable' => 'Regular Rates',
                        'value' => [
                            [
                                'key' => 'regular_day',
                                'label' => 'Regular Day',
                                'order' => 1,
                                'type' => 'number',
                                'readable' => '125%',
                                'value' => '1.25'
                            ],
                            [
                                'key' => 'rest_day',
                                'label' => 'Rest Day',
                                'order' => 2,
                                'type' => 'number',
                                'readable' => '169%',
                                'value' => '1.69'
                            ],
                            [
                                'key' => 'special_holiday_overtime',
                                'label' => 'Special Holiday',
                                'order' => 3,
                                'type' => 'number',
                                'readable' => '169%',
                                'value' => '1.69'
                            ],
                            [
                                'key' => 'special_holiday_and_rest_day_overtime',
                                'label' => 'Special Holiday & Rest Day',
                                'order' => 4,
                                'type' => 'number',
                                'readable' => '195%',
                                'value' => '1.95'
                            ],
                            [
                                'key' => 'legal_holiday_overtime',
                                'label' => 'Legal Holiday',
                                'order' => 5,
                                'type' => 'number',
                                'readable' => '260%',
                                'value' => '2.6'
                            ],
                            [
                                'key' => 'legal_holiday_and_rest_day_overtime',
                                'label' => 'Legal Holiday & Rest Day',
                                'order' => 6,
                                'type' => 'number',
                                'readable' => '338%',
                                'value' => '3.38'
                            ],
                            [
                                'key' => 'double_holiday_overtime',
                                'label' => 'Double Holiday',
                                'order' => 7,
                                'type' => 'number',
                                'readable' => '390%',
                                'value' => '3.9'
                            ],
                            [
                                'key' => 'double_holiday_and_rest_day_overtime',
                                'label' => 'Double Holiday & Rest Day',
                                'order' => 8,
                                'type' => 'number',
                                'readable' => '507%',
                                'value' => '5.07'
                            ],
                        ]
                    ],
                    [
                        'key' => 'night_differential_rates',
                        'label' => 'Night Differential Rates',
                        'order' => 2,
                        'type' => 'array',
                        'readable' => 'Night Differential Rates',
                        'value' => [
                            [
                                'key' => 'regular_day',
                                'label' => 'Regular Day',
                                'order' => 1,
                                'type' => 'number',
                                'readable' => '137.5%',
                                'value' => '1.375'
                            ],
                            [
                                'key' => 'rest_day',
                                'label' => 'Rest Day',
                                'order' => 2,
                                'type' => 'number',
                                'readable' => '185.9%',
                                'value' => '1.859'
                            ],
                            [
                                'key' => 'special_holiday',
                                'label' => 'Special Holiday',
                                'order' => 3,
                                'type' => 'number',
                                'readable' => '185.9%',
                                'value' => '1.859'
                            ],
                            [
                                'key' => 'special_holiday_and_rest_day',
                                'label' => 'Special Holiday & Rest Day',
                                'order' => 4,
                                'type' => 'number',
                                'readable' => '214.5%',
                                'value' => '2.145'
                            ],
                            [
                                'key' => 'legal_holiday_rate',
                                'label' => 'Legal Holiday',
                                'order' => 5,
                                'type' => 'number',
                                'readable' => '286%',
                                'value' => '2.86'
                            ],
                            [
                                'key' => 'legal_holiday_and_rest_day',
                                'label' => 'Legal Holiday & Rest Day',
                                'order' => 6,
                                'type' => 'number',
                                'readable' => '371.8%',
                                'value' => '3.718'
                            ],
                            [
                                'key' => 'double_holiday',
                                'label' => 'Double Holiday',
                                'order' => 7,
                                'type' => 'number',
                                'readable' => '429%',
                                'value' => '4.29'
                            ],
                            [
                                'key' => 'double_holiday_and_rest_day',
                                'label' => 'Double Holiday & Rest Day',
                                'order' => 8,
                                'type' => 'number',
                                'readable' => '557.7%',
                                'value' => '5.577'
                            ],
                        ]
                    ]
                ]
            ],
            ['name' => 'Standard-Meal', 'formulable_type' => Formulable::EARNINGS ,'component_type' => Compensation::REGULAR_ALLOWANCE, 'interpolation' => false],
            ['name' => 'Standard-Tardiness', 'formulable_type' => Formulable::DEDUCTIONS  ,'component_type' => Deduction::DEDUCTION, 'interpolation' => false],
            ['name' => 'Standard-Absent', 'formulable_type' => Formulable::DEDUCTIONS  ,'component_type' => Deduction::DEDUCTION, 'interpolation' => false],
            ['name' => 'Standard-SSS-Employed-Contribution', 'formulable_type' => Formulable::DEDUCTIONS  ,'component_type' => Deduction::CONTRIBUTION, 'interpolation' => false],
            ['name' => 'Standard-Philhealth-Contribution', 'formulable_type' => Formulable::DEDUCTIONS  ,'component_type' => Deduction::CONTRIBUTION, 'interpolation' => false],
            ['name' => 'Standard-Pagibig-Contribution', 'formulable_type' => Formulable::DEDUCTIONS ,'component_type' => Deduction::CONTRIBUTION, 'interpolation' => false],
            ['name' => 'Standard-Taxable-Income', 'formulable_type' => Formulable::TAXABLE_INCOME ,'component_type' => null, 'interpolation' => true],
            ['name' => 'Standard-Nontaxable-Income', 'formulable_type' => Formulable::NONTAXABLE_INCOME ,'component_type' => null, 'interpolation' => true],
            ['name' => 'Standard-Compensation-Tax', 'formulable_type' => Formulable::INCOME_TAX ,'component_type' => IncomeTax::COMPENSATION_TAX, 'interpolation' => false],
            ['name' => 'Standard-Net-Income', 'formulable_type' => Formulable::NET_INCOME ,'component_type' => null, 'interpolation' => true]
        ];

        foreach ($formulaPresets as $formula) {
            Formula::create($formula);
        }

        /**************************************************************************************************************************************************************************************************************/

        //Superadmin
        $superAdmin = User::factory()->superAdmin()->create(['name' => 'kim.123', 'email' => 'luxere20@gmail.com', 'timezone' => 'Asia/Manila']);

        //Account 1001
        $account1001 = Account::factory()->standard()->create(['number' => 'ACCOUNT20251001', 'ulid' => Str::ulid(), 'date_registered' => Carbon::now()->toDateTimeString()]);
        //Account 1002
        $account1002 = Account::factory()->standard()->create(['number' => 'ACCOUNT20251002', 'ulid' => Str::ulid(), 'date_registered' => Carbon::now()->toDateTimeString(),]);
        //Account 1003
        $account1003 = Account::factory()->standard()->create(['number' => 'ACCOUNT20251003', 'ulid' => Str::ulid(), 'date_registered' => Carbon::now()->toDateTimeString(),]);

        //Account 1001 Companies
        $company1001A = $account1001->companies()->create(['name' => 'Company 1001-A', 'code' => '1001-A', 'timezone' => 'Asia/Manila', 'ulid' => Str::ulid(),]);

        //Account 1002 Companies
        $company1002A = $account1002->companies()->create(['name' => 'Company 1002-A', 'code' => '1002-A', 'timezone' => 'Asia/Manila', 'ulid' => Str::ulid(),]);
        $company1002B = $account1002->companies()->create(['name' => 'Company 1002-B', 'code' => '1002-B', 'timezone' => 'Asia/Manila', 'ulid' => Str::ulid(),]);
        $company1002C = $account1002->companies()->create(['name' => 'Company 1002-C', 'code' => '1002-C', 'timezone' => 'Asia/Manila', 'ulid' => Str::ulid(),]);

        //Account 1001 Companies
        $company1003A = $account1003->companies()->create(['name' => 'Company 1003-A', 'code' => '1003-A', 'timezone' => 'Asia/Manila', 'ulid' => Str::ulid(),]);

        //Account 1002User01
        $account1002User01 = User::factory()->default()->create(['name' => '1002.user.1', 'email' => 'luxere20@gmail.com']);
        $account1002User02 = User::factory()->default()->create(['name' => '1002.user.2', 'email' => 'luxere20@gmail.com']);
        $account1002User03 = User::factory()->default()->create(['name' => '1002.user.3', 'email' => 'luxere20@gmail.com']);

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
        /**************************************************************************************************************************************************************************************************************/

        //Company 1002-B, 1002-C Salary Statement Modules
        foreach (self::$salaryStatementModules as $salaryStatementModule) {
            $company1002B->salaryStatementModules()->create($salaryStatementModule);
            $company1002C->salaryStatementModules()->create($salaryStatementModule);
        }

        $formulas = Formula::all();

        //Company 1002-A, 1002-B, 1002-C Assign Formula Presets
        foreach ($formulas as $formula) {
            $company1002A->formulas()->syncWithoutDetaching([$formula->id => ['settings' => isset($formula->default_settings->cast) ? json_encode($formula->default_settings->cast) : null]]);
            $company1002B->formulas()->syncWithoutDetaching([$formula->id => ['settings' => isset($formula->default_settings->cast) ? json_encode($formula->default_settings->cast) : null]]);
            $company1002C->formulas()->syncWithoutDetaching([$formula->id => ['settings' => isset($formula->default_settings->cast) ? json_encode($formula->default_settings->cast) : null]]);
        }

        //Company 1002-B Pay Period Preset of End of Month Cut-off
        $company1002B->payPeriodSetting()->create([
            'days_to_pay_after_cut_off' => 5,
            'time_period_preset_reference' => $endOfMonthCutOffPeriodPreset['name'],
            'monthly_pay_period' => $endOfMonthCutOffPeriodPreset['monthly_period'],
            'semimonthly_pay_period' => $endOfMonthCutOffPeriodPreset['semimonthly_period'],
        ]);

        //Company 1002-C Pay Period Preset of 25th Cut-off
        $company1002C->payPeriodSetting()->create([
            'days_to_pay_after_cut_off' => 5,
            'time_period_preset_reference' => $twentyFifthCutOffPeriodPreset['name'],
            'monthly_pay_period' => $twentyFifthCutOffPeriodPreset['monthly_period'],
            'semimonthly_pay_period' => $twentyFifthCutOffPeriodPreset['semimonthly_period'],
        ]);

        //Company 1002-B, 1002-C Pre-create Compensations
        $compensationsPresets = [
            ['name' => 'Basic Salary', 'assignable' => true, 'type' => Compensation::SALARY, 'formula' => 'Standard-Salary'],
            ['name' => 'Meal Allowance', 'assignable' => true, 'type' => Compensation::REGULAR_ALLOWANCE, 'formula' => 'Standard-Meal'],
            ['name' => 'Overtime', 'assignable' => true, 'type' => Compensation::OVERTIME, 'formula' => 'Standard-Overtime'],
        ];

        foreach ($compensationsPresets as $index => $compensationPreset) {
            $this->createPayrollComponent($company1002B, $index, Formulable::EARNINGS, 'compensations', $compensationPreset);
            $this->createPayrollComponent($company1002C, $index, Formulable::EARNINGS, 'compensations', $compensationPreset);
        }

        //Company 1002-B, 1002-C Pre-create Deductions
        $deductionsPresets = [
            ['name' => 'Tardiness', 'assignable' => true, 'type' => Deduction::DEDUCTION, 'formula' => 'Standard-Tardiness'],
            ['name' => 'Absent', 'assignable' => true, 'type' => Deduction::DEDUCTION, 'formula' => 'Standard-Absent'],
            ['name' => 'SSS-Employed', 'assignable' => true, 'type' => Deduction::CONTRIBUTION, 'formula' => 'Standard-SSS-Employed-Contribution'],
        ];

        foreach ($deductionsPresets as $index => $deductionsPreset) {
            $this->createPayrollComponent($company1002B, $index, Formulable::DEDUCTIONS, 'deductions', $deductionsPreset);
            $this->createPayrollComponent($company1002C, $index, Formulable::DEDUCTIONS, 'deductions', $deductionsPreset);
        }

        //Company 1002-B, 1002-C Pre-create Income Taxes
        $incomeTaxesPresets = [
            ['name' => 'Compensation Tax', 'assignable' => true, 'type' => IncomeTax::COMPENSATION_TAX, 'formula' => 'Standard-Compensation-Tax'],
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

        //Create Employee Info A1001 to Company 1002-A
        $employeeA1001 = $account1002User01->employees()->create([
            'ulid' => Str::ulid(),
            'company_id' => $company1002A->id,
            'number' => 'A1001',
            'given_name' => 'Employee 01',
            'middle_name' => 'A',
            'family_name' => '1002',
            'birth_date' => '1990-01-01',
            'gender' => Gender::FEMALE,
            'marital_status' => MaritalStatus::SINGLE,
        ]);

        //Create Employee Info B1001 to Company 1002-B
        $employeeB1001 = $account1002User02->employees()->create([
            'ulid' => Str::ulid(),
            'company_id' => $company1002B->id,
            'department_id' => $company1002B->departments()->where('name', 'Accounts Payable')->first()->id,
            'designation_id' => $company1002B->designations()->where('name', 'Accounting Staff')->first()->id,
            'number' => 'B1001',
            'given_name' => 'Employee 01',
            'middle_name' => 'B',
            'family_name' => '1002',
            'birth_date' => '1990-01-01',
            'gender' => Gender::FEMALE,
            'marital_status' => MaritalStatus::SINGLE,
        ]);

        //Create Employee Info C1001 to Company 1002-C
        $employeeC1001 = $account1002User01->employees()->create([
            'ulid' => Str::ulid(),
            'company_id' => $company1002C->id,
            'department_id' => $company1002C->departments()->where('name', 'HR')->first()->id,
            'designation_id' => $company1002C->designations()->where('name', 'HR Manager')->first()->id,
            'number' => 'C1001',
            'given_name' => 'Employee 01',
            'middle_name' => 'C',
            'family_name' => '1002',
            'birth_date' => '1990-01-01',
            'gender' => Gender::FEMALE,
            'marital_status' => MaritalStatus::SINGLE,
        ]);

        //Create Employee Info C1002 to Company 1002-C
        $employeeC1002 = $account1002User02->employees()->create([
            'ulid' => Str::ulid(),
            'company_id' => $company1002C->id,
            'department_id' => $company1002C->departments()->where('name', 'Accounts Payable')->first()->id,
            'designation_id' => $company1002C->designations()->where('name', 'Accounting Staff')->first()->id,
            'manager_id' => $account1002User01->id,
            'number' => 'C1002',
            'given_name' => 'Employee 02',
            'middle_name' => 'C',
            'family_name' => '1002',
            'birth_date' => '1990-01-01',
            'gender' => Gender::FEMALE,
            'marital_status' => MaritalStatus::SINGLE,
        ]);

        //Create Employee Info C1003 to Company 1002-C
        $employeeC1003 = $account1002User03->employees()->create([
            'ulid' => Str::ulid(),
            'company_id' => $company1002C->id,
            'department_id' => $company1002C->departments()->where('name', 'Accounts Payable')->first()->id,
            'designation_id' => $company1002C->designations()->where('name', 'Accounting Staff')->first()->id,
            'manager_id' => $account1002User01->id,
            'number' => 'C1003',
            'given_name' => 'Employee 03',
            'middle_name' => 'C',
            'family_name' => '1002',
            'birth_date' => '1990-01-01',
            'gender' => Gender::NOT_SPECIFIED,
            'marital_status' => MaritalStatus::SINGLE,
        ]);

        /**************************************************************************************************************************************************************************************************************/

        //Company 1002-B Compensations
        $company1002BBasicSalary = $company1002B->compensations->where('name', 'Basic Salary')->where('type', Compensation::SALARY)->first();
        $company1002BMealAllowance = $company1002B->compensations->where('name', 'Meal Allowance')->where('type', Compensation::REGULAR_ALLOWANCE)->first();
        $company1002BOvertime = $company1002B->compensations->where('name', 'Overtime')->where('type', Compensation::OVERTIME)->first();

        //Company 1002-B Deductions
        $company1002BTardiness = $company1002B->deductions->where('name', 'Tardiness')->where('type', Deduction::DEDUCTION)->first();
        $company1002BAbsent = $company1002B->deductions->where('name', 'Absent')->where('type', Deduction::DEDUCTION)->first();
        $company1002BSSSEmployed = $company1002B->deductions->where('name', 'SSS-Employed')->where('type', Deduction::CONTRIBUTION)->first();

        //Company 1002-B Income Taxes
        $company1002BCompensationTax = $company1002B->incomeTaxes->where('name', 'Compensation Tax')->where('type', IncomeTax::COMPENSATION_TAX)->first();

        //Create Compensations for Employee B1001
        $employeeB1001->payrollComponents()->create(['payroll_componentable_id' => $company1002BBasicSalary->id, 'payroll_componentable_type' => 'compensation', 'amount' => '1200.14', 'pay_period' => PayPeriod::MONTHLY, 'pay_type' => PayType::BY_ATTENDANCE, 'pay_frequency' => PayFrequency::MONTHLY]);
        $employeeB1001->payrollComponents()->create(['payroll_componentable_id' => $company1002BMealAllowance->id, 'payroll_componentable_type' => 'compensation', 'amount' => '200', 'pay_period' => PayPeriod::MONTHLY, 'pay_type' => PayType::BY_ATTENDANCE, 'pay_frequency' => PayFrequency::MONTHLY]);
        $employeeB1001->payrollComponents()->create(['payroll_componentable_id' => $company1002BOvertime->id, 'payroll_componentable_type' => 'compensation']);
        //Create Deductions for Employee B1001
        $employeeB1001->payrollComponents()->create(['payroll_componentable_id' => $company1002BTardiness->id, 'payroll_componentable_type' => 'deduction']);
        $employeeB1001->payrollComponents()->create(['payroll_componentable_id' => $company1002BAbsent->id, 'payroll_componentable_type' => 'deduction']);
        $employeeB1001->payrollComponents()->create(['payroll_componentable_id' => $company1002BSSSEmployed->id, 'payroll_componentable_type' => 'deduction']);
        //Create Income Tax for Employee B1001
        $employeeB1001->payrollComponents()->create(['payroll_componentable_id' => $company1002BCompensationTax->id, 'payroll_componentable_type' => 'income_tax']);

        //Company 1002-C Compensations
        $company1002CBasicSalary = $company1002C->compensations->where('name', 'Basic Salary')->where('type', Compensation::SALARY)->first();
        $company1002CMealAllowance = $company1002C->compensations->where('name', 'Meal Allowance')->where('type', Compensation::REGULAR_ALLOWANCE)->first();
        $company1002COvertime = $company1002C->compensations->where('name', 'Overtime')->where('type', Compensation::OVERTIME)->first();

        //Company 1002-C Deductions
        $company1002CTardiness = $company1002C->deductions->where('name', 'Tardiness')->where('type', Deduction::DEDUCTION)->first();
        $company1002CAbsent = $company1002C->deductions->where('name', 'Absent')->where('type', Deduction::DEDUCTION)->first();
        $company1002CSSSEmployed = $company1002C->deductions->where('name', 'SSS-Employed')->where('type', Deduction::CONTRIBUTION)->first();

        //Company 1002-C Income Taxes
        $company1002CCompensationTax = $company1002C->incomeTaxes->where('name', 'Compensation Tax')->where('type', IncomeTax::COMPENSATION_TAX)->first();

        //Create Compensations for Employee C1001
        $employeeC1001->payrollComponents()->create(['payroll_componentable_id' => $company1002CBasicSalary->id, 'payroll_componentable_type' => 'compensation', 'amount' => '1200.14', 'pay_period' => PayPeriod::MONTHLY, 'pay_type' => PayType::BY_ATTENDANCE, 'pay_frequency' => PayFrequency::MONTHLY]);
        $employeeC1001->payrollComponents()->create(['payroll_componentable_id' => $company1002CMealAllowance->id, 'payroll_componentable_type' => 'compensation', 'amount' => '200', 'pay_period' => PayPeriod::MONTHLY, 'pay_type' => PayType::BY_ATTENDANCE, 'pay_frequency' => PayFrequency::MONTHLY]);
        $employeeC1001->payrollComponents()->create(['payroll_componentable_id' => $company1002COvertime->id, 'payroll_componentable_type' => 'compensation']);
        //Create Deductions for Employee C1001
        $employeeC1001->payrollComponents()->create(['payroll_componentable_id' => $company1002CTardiness->id, 'payroll_componentable_type' => 'deduction']);
        $employeeC1001->payrollComponents()->create(['payroll_componentable_id' => $company1002CAbsent->id, 'payroll_componentable_type' => 'deduction']);
        $employeeC1001->payrollComponents()->create(['payroll_componentable_id' => $company1002CSSSEmployed->id, 'payroll_componentable_type' => 'deduction']);
        //Create Income Tax for Employee C1001
        $employeeC1001->payrollComponents()->create(['payroll_componentable_id' => $company1002CCompensationTax->id, 'payroll_componentable_type' => 'income_tax']);

        //Create Compensations for Employee C1002
        $employeeC1002->payrollComponents()->create(['payroll_componentable_id' => $company1002CBasicSalary->id, 'payroll_componentable_type' => 'compensation', 'amount' => '100', 'pay_period' => PayPeriod::MONTHLY, 'pay_type' => PayType::BY_ATTENDANCE, 'pay_frequency' => PayFrequency::MONTHLY]);
        $employeeC1002->payrollComponents()->create(['payroll_componentable_id' => $company1002CMealAllowance->id, 'payroll_componentable_type' => 'compensation', 'amount' => '10', 'pay_period' => PayPeriod::MONTHLY, 'pay_type' => PayType::BY_ATTENDANCE, 'pay_frequency' => PayFrequency::MONTHLY]);
        $employeeC1002->payrollComponents()->create(['payroll_componentable_id' => $company1002COvertime->id, 'payroll_componentable_type' => 'compensation']);
        //Create Deductions for Employee C1002
        $employeeC1002->payrollComponents()->create(['payroll_componentable_id' => $company1002CTardiness->id, 'payroll_componentable_type' => 'deduction']);
        $employeeC1002->payrollComponents()->create(['payroll_componentable_id' => $company1002CAbsent->id, 'payroll_componentable_type' => 'deduction']);
        $employeeC1002->payrollComponents()->create(['payroll_componentable_id' => $company1002CSSSEmployed->id, 'payroll_componentable_type' => 'deduction']);
        //Create Income Tax for Employee C1002
        $employeeC1002->payrollComponents()->create(['payroll_componentable_id' => $company1002CCompensationTax->id, 'payroll_componentable_type' => 'income_tax']);

        //Create Compensations for Employee C1003
        $employeeC1003->payrollComponents()->create(['payroll_componentable_id' => $company1002CBasicSalary->id, 'payroll_componentable_type' => 'compensation', 'amount' => '420', 'pay_period' => PayPeriod::MONTHLY, 'pay_type' => PayType::BY_ATTENDANCE, 'pay_frequency' => PayFrequency::MONTHLY]);
        $employeeC1003->payrollComponents()->create(['payroll_componentable_id' => $company1002CMealAllowance->id, 'payroll_componentable_type' => 'compensation', 'amount' => '20', 'pay_period' => PayPeriod::MONTHLY, 'pay_type' => PayType::BY_ATTENDANCE, 'pay_frequency' => PayFrequency::MONTHLY]);
        $employeeC1003->payrollComponents()->create(['payroll_componentable_id' => $company1002COvertime->id, 'payroll_componentable_type' => 'compensation']);
        //Create Deductions for Employee C1003
        $employeeC1003->payrollComponents()->create(['payroll_componentable_id' => $company1002CTardiness->id, 'payroll_componentable_type' => 'deduction']);
        $employeeC1003->payrollComponents()->create(['payroll_componentable_id' => $company1002CAbsent->id, 'payroll_componentable_type' => 'deduction']);
        $employeeC1003->payrollComponents()->create(['payroll_componentable_id' => $company1002CSSSEmployed->id, 'payroll_componentable_type' => 'deduction']);
        //Create Income Tax for Employee C1003
        $employeeC1003->payrollComponents()->create(['payroll_componentable_id' => $company1002CCompensationTax->id, 'payroll_componentable_type' => 'income_tax']);
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
}
