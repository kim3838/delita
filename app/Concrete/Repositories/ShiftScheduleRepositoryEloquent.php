<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\ShiftScheduleRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\Company;
use App\Models\ShiftSchedule;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class ShiftScheduleRepositoryEloquent extends BaseRepositoryEloquent implements ShiftScheduleRepository
{
    public function model(): string
    {
        return ShiftSchedule::class;
    }

    public function list($filters)
    {
        $queryBuilder = $this->model::getQuery()
            ->when($filters->shift_id ?? false, function ($builder, $value) {
                $builder->where(DB::raw("shift_schedules.shift_id"), $value);
            })
            ->orderBy('week_day', 'ASC')
            ->select([
                'shift_schedules.*',
            ]);

        return $this->hydrateCollection($queryBuilder->get(), $this->model);
    }

    public function preset($companyId)
    {
        $companyTimezone = Company::find($companyId)->timezone;

        $preset = [];

        $weekdays = [
            CarbonInterface::SUNDAY,
            CarbonInterface::MONDAY,
            CarbonInterface::TUESDAY,
            CarbonInterface::WEDNESDAY,
            CarbonInterface::THURSDAY,
            CarbonInterface::FRIDAY,
            CarbonInterface::SATURDAY,
        ];

        $restDays = [
            CarbonInterface::SUNDAY,
            CarbonInterface::SATURDAY,
        ];

        $dayoffs = [
            CarbonInterface::SUNDAY,
            CarbonInterface::SATURDAY,
        ];

        foreach ($weekdays as $weekday) {

            $dayOff = in_array($weekday, $dayoffs);

            if($dayOff){
                $preset[] = [
                    'week_day' => $weekday,
                    'is_rest_day' => in_array($weekday, $restDays),
                    'is_day_off' => true,
                    'timezone' => null,
                    'is_flexible' => false,
                    'work_start' => null,
                    'work_end' => null,
                    'total_work_hours_with_breaks' => null,
                    'has_lunch_break' => false,
                    'lunch_break_start' => null,
                    'lunch_break_end' => null,
                    'total_lunch_break_hours' => null
                ];

            } else {

                $preset[] = [
                    'week_day' => $weekday,
                    'is_rest_day' => in_array($weekday, $restDays),
                    'is_day_off' => false,
                    'timezone' => $companyTimezone,
                    'is_flexible' => false,
                    'work_start' => '09:00',
                    'work_end' => '17:00',
                    'total_work_hours_with_breaks' => '08:00',
                    'has_lunch_break' => true,
                    'lunch_break_start' => '12:00',
                    'lunch_break_end' => '13:00',
                    'total_lunch_break_hours' => '01:00'
                ];
            }
        }

        return $this->model::hydrate($preset);
    }
}
