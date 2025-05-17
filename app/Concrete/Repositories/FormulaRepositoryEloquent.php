<?php

namespace App\Concrete\Repositories;

use App\Concrete\BaseRepositoryEloquent;
use App\Enums\Formulable;
use App\Models\CompanyFormula;
use App\Models\Formula;
use Illuminate\Support\Facades\Request;

class FormulaRepositoryEloquent extends BaseRepositoryEloquent
{
    public function model(): string
    {
        return Formula::class;
    }

    public function selection()
    {
        $filters = json_decode(Request::get('filters'));

        $queryBuilder = CompanyFormula::getQuery()
            ->leftJoin('formulas', 'formulas.id', '=', 'company_formula.formula_id')
            ->leftJoin('companies', 'companies.id', '=', 'company_formula.company_id')
            ->when($filters->company_id ?? false, function ($builder, $value) {
                $builder->where('companies.id', $value);
            })
            ->when(Formulable::tryFrom($filters->formulable_type) !== null, function ($builder) use ($filters) {
                $builder->where('formulas.formulable_type', $filters->formulable_type);
            })
            ->when(isset($filters->component_type), function ($builder) use ($filters) {
                $builder->where('formulas.component_type', $filters->component_type);
            })
            ->select([
                'formulas.*'
            ]);

        return $this->model::hydrate($queryBuilder->get()->toArray());
    }
}
