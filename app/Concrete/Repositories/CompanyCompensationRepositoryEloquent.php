<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\CompanyCompensationRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\Compensation;
use App\Models\Hydrations\CompanyCompensation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CompanyCompensationRepositoryEloquent extends BaseRepositoryEloquent implements CompanyCompensationRepository
{
    public function model(): string
    {
        return CompanyCompensation::class;
    }

    public function list($filters): Collection
    {
        $queryBuilder = Compensation::query()->getQuery()
            ->leftJoin('company_formula', function ($join) {
                $join->on(DB::raw("company_formula.id"), '=', DB::raw("compensations.company_formula_id"))
                    ->where(DB::raw("company_formula.company_id"), '=', DB::raw("compensations.company_id"));
            })
            ->leftJoin('formulas', 'formulas.id', '=', 'company_formula.formula_id')
            ->when($filters->company_id ?? false, function ($builder, $value) {
                $builder->where(DB::raw("compensations.company_id"), $value);
            })
            ->when(isset($filters->assignable), function ($builder) use($filters){
                $builder->where(DB::raw("compensations.assignable"), intval($filters->assignable));
            })
            ->select([
                'compensations.id AS id',
                'compensations.company_id AS company_id',
                'compensations.code AS code',
                'compensations.name AS name',
                'compensations.order AS order',
                'compensations.assignable AS assignable',
                'compensations.type AS type',
                'compensations.component_sub_type AS component_sub_type',
                'compensations.company_formula_id AS company_formula_id',
                'formulas.name AS formula',
                'company_formula.settings AS settings'
            ])
            ->orderBy('order', 'ASC');

        return $this->hydrateCollection($queryBuilder->get(), $this->model());
    }
}
