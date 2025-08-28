<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\CompensationRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\Compensation;
use App\Traits\PayrollComponentChain;
use Illuminate\Support\Facades\DB;

class CompensationRepositoryEloquent extends BaseRepositoryEloquent implements CompensationRepository
{
    use PayrollComponentChain;

    public function model(): string
    {
        return Compensation::class;
    }

    public function selection($filters)
    {
        $queryBuilder = $this->model->getQuery()
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

        return $this->model::hydrate($queryBuilder->get()->toArray());
    }

    public function delete($id)
    {
        $model = $this->model::findOrfail($id);

        $this->deleteEmployeeAssignedComponentable('compensation', $model->id);

        return $model->delete();
    }
}
