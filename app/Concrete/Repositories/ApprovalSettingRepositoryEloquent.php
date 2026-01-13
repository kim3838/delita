<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\ApprovalSettingRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\ApprovalSetting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ApprovalSettingRepositoryEloquent extends BaseRepositoryEloquent implements ApprovalSettingRepository
{
    public function model(): string
    {
        return ApprovalSetting::class;
    }

    public function list($filters): Collection
    {
        $queryBuilder = $this->model::query()->getQuery()
            ->when($filters->company_id ?? false, function ($builder, $value) {
                $builder->where(DB::raw("approval_settings.company_id"), $value);
            })
            ->select([
                'approval_settings.*'
            ]);

        return $this->hydrateCollection($queryBuilder->get(), $this->model());
    }
}
