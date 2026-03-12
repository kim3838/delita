<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\CompanyDeductionRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\Deduction;
use App\Models\Hydrations\CompanyDeduction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CompanyDeductionRepositoryEloquent extends BaseRepositoryEloquent implements CompanyDeductionRepository
{
    public function model(): string
    {
        return CompanyDeduction::class;
    }

    public function list($filters): Collection
    {
        $queryBuilder = Deduction::query()->getQuery()
            ->leftJoin('company_formula', function ($join) {
                $join->on(DB::raw("company_formula.id"), '=', DB::raw("deductions.company_formula_id"))
                    ->where(DB::raw("company_formula.company_id"), '=', DB::raw("deductions.company_id"));
            })
            ->leftJoin('formulas', 'formulas.id', '=', 'company_formula.formula_id')
            ->when($filters->company_id ?? false, function ($builder, $value) {
                $builder->where(DB::raw("deductions.company_id"), $value);
            })
            ->when(isset($filters->assignable), function ($builder) use($filters){
                $builder->where(DB::raw("deductions.assignable"), intval($filters->assignable));
            })
            ->select([
                'deductions.id AS id',
                'deductions.company_id AS company_id',
                'deductions.code AS code',
                'deductions.name AS name',
                'deductions.order AS order',
                'deductions.assignable AS assignable',
                'deductions.type AS type',
                'deductions.component_sub_type AS component_sub_type',
                'deductions.company_formula_id AS company_formula_id',
                'formulas.name AS formula',
                'company_formula.settings AS settings'
            ])
            ->orderBy('order', 'ASC');

        return $this->hydrateCollection($queryBuilder->get(), $this->model());
    }
}
