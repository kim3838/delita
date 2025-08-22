<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\FormulaRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Enums\Formulable;
use App\Models\Formula;

class FormulaRepositoryEloquent extends BaseRepositoryEloquent implements FormulaRepository
{
    public function model(): string
    {
        return Formula::class;
    }

    public function list($filters)
    {
        $queryBuilder = $this->model::getQuery()
            ->when(!empty($filters->formulable_types) && is_array($filters->formulable_types), function ($builder) use ($filters) {

                $filteredFormulableTypes = array_filter($filters->formulable_types, function($formulableType) {
                    return Formulable::tryFrom($formulableType) !== null;
                });

                foreach ($filteredFormulableTypes as $index => $formulableType) {

                    $builder->{$index > 0 ? 'orWhere' : 'where'}(function($clause) use($formulableType, $filters){

                        $clause->where('formulas.formulable_type', $formulableType);

                        if($formulableType == Formulable::EARNINGS->value){
                            $clause->when(!empty($filters->earning_components) && is_array($filters->earning_components), function ($builder) use ($filters) {
                                $builder->whereIn('formulas.component_type', $filters->earning_components);
                            });
                        } else if($formulableType == Formulable::DEDUCTIONS->value){
                            $clause->when(!empty($filters->deduction_components) && is_array($filters->deduction_components), function ($builder) use ($filters) {
                                $builder->whereIn('formulas.component_type', $filters->deduction_components);
                            });
                        } else if($formulableType == Formulable::INCOME_TAX->value){
                            $clause->when(!empty($filters->income_tax_components) && is_array($filters->income_tax_components), function ($builder) use ($filters) {
                                $builder->whereIn('formulas.component_type', $filters->income_tax_components);
                            });
                        }
                    });
                }
            })
            ->when(!empty($filters->interpolations) && is_array($filters->interpolations), function ($builder) use ($filters) {
                $builder->whereIn('formulas.interpolation', $filters->interpolations);
            })
            ->when($filters->search ?? false, function($builder, $value){
                $builder->where(function($clause) use($value){
                    $clause->where('formulas.name', 'LIKE', ('%' . $value . '%'));
                });
            })
            ->select([
                'formulas.*'
            ])
            ->orderBy('formulas.formulable_type', 'ASC')
            ->orderBy('formulas.component_type', 'ASC');

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, $this->model);
    }

    public function show($ulid)
    {
        $queryBuilder = $this->model::where('ulid', $ulid);

        return $queryBuilder->first();
    }
}
