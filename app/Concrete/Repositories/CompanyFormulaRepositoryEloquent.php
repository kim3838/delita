<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\CompanyFormulaRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Enums\Formulable;
use App\Models\CompanyFormula;
use App\Models\Hydrations\CompanyFormula\FormulaSetting;
use App\Models\Hydrations\CompanyFormula\Selection as FormulaSelection;

class CompanyFormulaRepositoryEloquent extends BaseRepositoryEloquent implements CompanyFormulaRepository
{
    public function model(): string
    {
        return CompanyFormula::class;
    }

    public function list($filters)
    {
        $queryBuilder = $this->model::getQuery()
            ->leftJoin('formulas', 'formulas.id', '=', 'company_formula.formula_id')
            ->leftJoin('companies', 'companies.id', '=', 'company_formula.company_id')
            ->when($filters->company_id ?? false, function ($builder, $value) {
                $builder->where('company_formula.company_id', $value);
            })
            ->when(!empty($filters->interpolations) && is_array($filters->interpolations), function ($builder) use ($filters) {
                $builder->whereIn('formulas.interpolation', $filters->interpolations);
            })
            ->select([
                'company_formula.id AS company_formula_id',
                'company_formula.company_id AS company_id',
                'company_formula.formula_id AS formula_id',
                'companies.code AS company_code',
                'companies.name AS company_name',
                'formulas.name AS formula_name',
                'formulas.interpolation AS formula_is_interpolation',
                'formulas.formulable_type AS formulable_type',
                'formulas.component_type AS formulable_component_type',
                'company_formula.settings AS formula_settings',
            ])
            ->orderBy('formulas.formulable_type', 'ASC')
            ->orderBy('formulas.component_type', 'ASC');

        return FormulaSetting::hydrate($queryBuilder->get()->toArray());
    }

    public function selection($filters)
    {
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
