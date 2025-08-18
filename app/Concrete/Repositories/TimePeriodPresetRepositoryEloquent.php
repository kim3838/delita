<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\TimePeriodPresetRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Enums\TimePeriodType;
use App\Models\TimePeriodPreset;

class TimePeriodPresetRepositoryEloquent extends BaseRepositoryEloquent implements TimePeriodPresetRepository
{
    public function model(): string
    {
        return TimePeriodPreset::class;
    }

    public function endOfMonthPeriod()
    {
        return $this->model::where('type', TimePeriodType::PAY_FREQUENCY)
            ->where('name', 'end_of_month_cut_off')
            ->firstOrFail();
    }

    public function selection($filters)
    {
        $queryBuilder = $this->model::getQuery()
            ->when($filters->name ?? false, function ($builder, $value) {
                $builder->where('name', $value);
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

        return $this->model::hydrate($queryBuilder->get()->toArray());
    }
}
