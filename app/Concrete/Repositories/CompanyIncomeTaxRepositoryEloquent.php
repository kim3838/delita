<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\CompanyIncomeTaxRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\Hydrations\CompanyIncomeTax;
use App\Models\IncomeTax;
use Illuminate\Support\Facades\DB;

class CompanyIncomeTaxRepositoryEloquent extends BaseRepositoryEloquent implements CompanyIncomeTaxRepository
{
    public function model(): string
    {
        return CompanyIncomeTax::class;
    }

    public function list($filters)
    {
        $queryBuilder = IncomeTax::getQuery()
            ->leftJoin('company_formula', function ($join) {
                $join->on(DB::raw("company_formula.id"), '=', DB::raw("income_taxes.company_formula_id"))
                    ->where(DB::raw("company_formula.company_id"), '=', DB::raw("income_taxes.company_id"));
            })
            ->leftJoin('formulas', 'formulas.id', '=', 'company_formula.formula_id')
            ->when($filters->company_id ?? false, function ($builder, $value) {
                $builder->where(DB::raw("income_taxes.company_id"), $value);
            })
            ->select([
                'income_taxes.id AS id',
                'income_taxes.company_id AS company_id',
                'income_taxes.code AS code',
                'income_taxes.name AS name',
                'income_taxes.order AS order',
                'income_taxes.assignable AS assignable',
                'income_taxes.type AS type',
                'income_taxes.company_formula_id AS company_formula_id',
                'formulas.name AS formula',
                'company_formula.settings AS settings'
            ])
            ->orderBy('order', 'ASC');

        return $this->model::hydrate($queryBuilder->get()->toArray());
    }
}
