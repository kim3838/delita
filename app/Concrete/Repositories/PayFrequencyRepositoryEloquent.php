<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\PayFrequencyRepository;
use App\Blueprint\Repositories\TimePeriodPresetRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Enums\CutOffType;
use App\Enums\PayFrequency as PayFrequencyEnum;
use App\Enums\WeekDay;
use App\Models\PayFrequency;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class PayFrequencyRepositoryEloquent extends BaseRepositoryEloquent implements PayFrequencyRepository
{
    public function model(): string
    {
        return PayFrequency::class;
    }

    public static function defaultPresets(): array
    {
        $endOfMonthTimePeriodPreset = App::make(TimePeriodPresetRepository::class)->endOfMonthPeriod();

        return [
            [
                'code' => 'DAILY',
                'order' => 1,
                'type' => PayFrequencyEnum::DAILY,
                'time_period_preset_id' => null,
                'period' => null,
                'cutoff_type' => null,
                'cut_off_value' => null,
                'days_span' => null,
            ],[
                'code' => 'WEEKLY',
                'order' => 2,
                'type' => PayFrequencyEnum::WEEKLY,
                'time_period_preset_id' => null,
                'period' => null,
                'cutoff_type' => CutOffType::WEEKDAY,
                'cut_off_value' => WeekDay::FRIDAY,
                'days_span' => 7,
            ],[
                'code' => 'SEMI_MONTHLY',
                'order' => 3,
                'type' => PayFrequencyEnum::SEMI_MONTHLY,
                'time_period_preset_id' => $endOfMonthTimePeriodPreset->id,
                'period' => $endOfMonthTimePeriodPreset->semimonthly_period,
                'cutoff_type' => null,
                'cut_off_value' => null,
                'days_span' => null,
            ],[
                'code' => 'MONTHLY',
                'order' => 4,
                'type' => PayFrequencyEnum::MONTHLY,
                'time_period_preset_id' => $endOfMonthTimePeriodPreset->id,
                'period' => $endOfMonthTimePeriodPreset->monthly_period,
                'cutoff_type' => null,
                'cut_off_value' => null,
                'days_span' => null,
            ],
        ];
    }

    public function list($filters): Collection
    {
        $queryBuilder = $this->model::query()->getQuery()
            ->when($filters->company_id ?? false, function ($builder, $value) {
                $builder->where(DB::raw("pay_frequencies.company_id"), $value);
            })
            ->select([
                'pay_frequencies.*'
            ])
            ->orderBy('order', 'ASC');

        return $this->hydrateCollection($queryBuilder->get(), $this->model());
    }

    public function selection($filters): Collection
    {
        $queryBuilder = $this->model::query()->getQuery()
            ->when($filters->company_id ?? false, function ($builder, $value) {
                $builder->where(DB::raw("pay_frequencies.company_id"), $value);
            })
            ->select([
                'pay_frequencies.id AS id',
                'pay_frequencies.code AS code',
                'pay_frequencies.type AS type',
            ])
            ->orderBy('order', 'ASC');

        return $this->hydrateCollection($queryBuilder->get(), $this->model());
    }
}
