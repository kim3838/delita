<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\FormulaRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Enums\Formulable;
use App\Enums\FormulableComponentSubType;
use App\Models\Formula;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class FormulaRepositoryEloquent extends BaseRepositoryEloquent implements FormulaRepository
{
    public function model(): string
    {
        return Formula::class;
    }

    public function paginate($filters): LengthAwarePaginator
    {
        $orders = [
            ['field' => 'formulas.formulable_type', 'direction' => 'ASC'],
            ['field' => 'formulas.component_type', 'direction' => 'ASC'],
            ['field' => 'formulas.name', 'direction' => 'ASC'],
        ];

        $queryBuilder = $this->model::query()->getQuery()
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
            ->when(!empty($filters->aggregations) && is_array($filters->aggregations), function ($builder) use ($filters) {
                $builder->whereIn('formulas.aggregation', $filters->aggregations);
            })
            ->when($filters->search ?? false, function($builder, $value){
                $builder->where(function($clause) use($value){
                    $clause->where('formulas.name', 'LIKE', "%$value%");
                });
            })
            ->select([
                'formulas.*'
            ]);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, $this->model());
    }

    public function show($identifier)
    {
        $queryBuilder = $this->model::query()->where('ulid', $identifier);

        return $queryBuilder->first();
    }

    public function defaultPresets(): Collection
    {
        return $this->model::query()
            ->whereIn('name', [
                'Standard-Basic-Pay',
                'Standard-Allowance',
                'Standard-Overtime',
                'Standard-Leave-Pay',
                'Standard-Holiday-Pay',
                'Manual-Earning',
                'Standard-Taxable-Income',
                'Standard-Nontaxable-Income',
                'Manual-Deduction',
                'Standard-Net-Income',
            ])
            ->orderBy('formulas.formulable_type', 'ASC')
            ->orderBy('formulas.component_type', 'ASC')
            ->orderBy('formulas.name', 'ASC')
            ->get();
    }

    public function formulableComponentSubTypeFormula(FormulableComponentSubType $componentSubType): ?Formula
    {
        $formulaName = null;

        switch($componentSubType){
            case FormulableComponentSubType::MANUAL_EARNING:
                $formulaName = 'Manual-Earning';
                break;
            case FormulableComponentSubType::MANUAL_DEDUCTION:
                $formulaName = 'Manual-Deduction';
                break;
        }

        return $this->model::query()->where('name', $formulaName)->first();
    }
}
