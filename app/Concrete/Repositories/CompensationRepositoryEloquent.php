<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\CompensationRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\Compensation;
use App\Traits\PayrollComponentChain;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CompensationRepositoryEloquent extends BaseRepositoryEloquent implements CompensationRepository
{
    use PayrollComponentChain;

    public function model(): string
    {
        return Compensation::class;
    }

    public function selection($filters): Collection
    {
        $queryBuilder = $this->model::query()->getQuery()
            ->when($filters->company_id ?? false, function ($builder, $value) {
                $builder->where(DB::raw("compensations.company_id"), $value);
            })
            ->when(isset($filters->assignable), function ($builder) use($filters){
                $builder->where(DB::raw("compensations.assignable"), intval($filters->assignable));
            })
            ->select([
                'compensations.id AS id',
                'compensations.code AS code',
                'compensations.name AS name',
                'compensations.type AS type',
                'compensations.assignable AS assignable',
            ])
            ->orderBy('order', 'ASC');

        return $this->hydrateCollection($queryBuilder->get(), $this->model());
    }

    public function delete($identifier): ?bool
    {
        $model = $this->model::query()->findOrfail($identifier);

        $this->deleteEmployeeAssignedComponentable('compensation', $model->id);

        return $model->delete();
    }

    public function batchDelete($ids): int
    {
        foreach ($ids as $id) {
            $this->delete($id);
        }

        return 1;
    }
}
