<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\TimePeriodPresetRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Enums\TimePeriodType;
use App\Models\TimePeriodPreset;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TimePeriodPresetRepositoryEloquent extends BaseRepositoryEloquent implements TimePeriodPresetRepository
{
    public function model(): string
    {
        return TimePeriodPreset::class;
    }

    public function endOfMonthPeriod()
    {
        return $this->model::query()
            ->where('type', TimePeriodType::PAY_FREQUENCY)
            ->where('name', 'end_of_month_cut_off')
            ->firstOrFail();
    }

    public function selection($filters): Collection
    {
        $queryBuilder = $this->model::query()->getQuery()
            ->when($filters->name ?? false, function ($builder, $value) {
                $builder->where('name', $value);
            })
            ->when(!empty($filters->time_period_preset_names) && is_array($filters->time_period_preset_names), function ($builder) use ($filters) {
                $builder->whereIn(DB::raw("name"), $filters->time_period_preset_names);
            })
            ->when(
                isset($filters->type) && TimePeriodType::tryFrom($filters->type) !== null,
                function ($builder) use ($filters) {
                    $builder->where('type', $filters->type);
                }
            )
            ->select([
                'id',
                'type',
                'name',
                'readable_name',
                'yearly_period',
                'monthly_period',
                'semimonthly_period',
            ]);

        return $this->hydrateCollection($queryBuilder->get(), $this->model());
    }
}
