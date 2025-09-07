<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\CompanyFormulaRepository;
use App\Blueprint\Repositories\CompensationRepository;
use App\Blueprint\Repositories\DeductionRepository;
use App\Blueprint\Repositories\FormulaRepository;
use App\Blueprint\Repositories\IncomeTaxRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Enums\Formulable;
use App\Facades\Fractal;
use App\Models\Company;
use App\Models\CompanyFormula;
use App\Models\Compensation;
use App\Models\Deduction;
use App\Models\Formula;
use App\Models\Hydrations\CompanyFormula\FormulaSetting;
use App\Models\Hydrations\CompanyFormula\Selection as FormulaSelection;
use App\Models\IncomeTax;
use App\Transformers\Formula\ItemTransformer as FormulaItemTransformer;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

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
            ->when($filters->company_ulid ?? false, function ($builder, $value) {
                $builder->where('companies.ulid', $value);
            })
            ->when(!empty($filters->aggregations) && is_array($filters->aggregations), function ($builder) use ($filters) {
                $builder->whereIn('formulas.aggregation', $filters->aggregations);
            })
            ->select([
                DB::raw("ROW_NUMBER() OVER() AS 'row_number'"),
                'company_formula.id AS company_formula_id',
                'company_formula.company_id AS company_id',
                'company_formula.formula_id AS formula_id',
                'companies.code AS company_code',
                'companies.name AS company_name',
                'formulas.ulid AS formula_ulid',
                'formulas.name AS formula_name',
                'formulas.aggregation AS formula_is_aggregation',
                'formulas.formulable_type AS formulable_type',
                'formulas.component_type AS formulable_component_type',
                'formulas.default_settings AS default_settings',
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

    public function sync($companyId, $formulas)
    {
        $companyFormulaPivotTemp = CompanyFormula::where('company_id', $companyId)
            ->get()
            ->map(function($item){

                return (object)[
                    'id' => $item->id,
                    'formula_id' => $item->formula_id
                ];
            });

        $sync = array_map(function ($formula) {

            $settings = empty($formula['settings']) ? null : json_encode($formula['settings']);

            return ['settings' => $settings];

        }, $formulas);

        $syncResult = Company::findOrFail($companyId)->formulas()->sync($sync);

        foreach ($companyFormulaPivotTemp->whereIn('formula_id', $syncResult['detached']) as $companyFormulaPivot){
            $formula = Formula::find($companyFormulaPivot->formula_id);

            if($formula->formulable_type->value == Formulable::EARNINGS->value){

                $compensations = Compensation::where('company_id', $companyId)->where('company_formula_id', $companyFormulaPivot->id)->get();

                foreach ($compensations as $compensation) {

                    App::make(CompensationRepository::class)->delete($compensation->id);
                }

            } else if($formula->formulable_type->value == Formulable::DEDUCTIONS->value){

                $deductions = Deduction::where('company_id', $companyId)->where('company_formula_id', $companyFormulaPivot->id)->get();

                foreach ($deductions as $deduction) {

                    App::make(DeductionRepository::class)->delete($deduction->id);
                }

            } else if($formula->formulable_type->value == Formulable::INCOME_TAX->value){

                $incomeTaxes = IncomeTax::where('company_id', $companyId)->where('company_formula_id', $companyFormulaPivot->id)->get();

                foreach ($incomeTaxes as $incomeTax) {

                    App::make(IncomeTaxRepository::class)->delete($incomeTax->id);
                }

            }
        }

        return $syncResult;
    }

    public function syncWithoutDetaching($companyId, $formulaUlids)
    {
        $sync = [];

        foreach ($formulaUlids as $ulid) {
            $formula = App::make(FormulaRepository::class)->show($ulid);
            $formula = $formula ? Fractal::item($formula, FormulaItemTransformer::class) : $formula;

            $settings = empty($formula['default_settings']) ? null : json_encode($formula['default_settings']);

            $sync[$formula['id']] = ['settings' => $settings];
        }

        return Company::findOrFail($companyId)->formulas()->syncWithoutDetaching($sync);
    }
}
