<?php

namespace App\Concrete\Repositories;

use App\Concrete\BaseRepositoryEloquent;
use App\Enums\Formulable;
use App\Models\CompanyFormula;
use App\Models\Hydrations\CompanyFormula\Selection as FormulaSelection;
use Illuminate\Support\Facades\Request;

class CompanyFormulaRepositoryEloquent extends BaseRepositoryEloquent
{
    public function model(): string
    {
        return CompanyFormula::class;
    }

    public function selection()
    {
        $filters = json_decode(Request::get('filters'));

        $queryBuilder = $this->model::getQuery()
            ->leftJoin('formulas', 'formulas.id', '=', 'company_formula.formula_id')
            ->leftJoin('companies', 'companies.id', '=', 'company_formula.company_id')
            ->when($filters->company_id ?? false, function ($builder, $value) {
                $builder->where('companies.id', $value);
            })
            ->when(
                isset($filters->formulable_type) && Formulable::tryFrom($filters->formulable_type) !== null,
                function ($builder) use ($filters) {
                    $builder->where('formulas.formulable_type', $filters->formulable_type);
                }
            )
            ->when(isset($filters->component_type), function ($builder) use ($filters) {
                $builder->where('formulas.component_type', $filters->component_type);
            })
            ->select([
                'company_formula.id AS id',
                'formulas.name AS name'
            ]);

        return FormulaSelection::hydrate($queryBuilder->get()->toArray());
    }
}
