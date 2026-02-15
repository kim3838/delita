<?php

namespace App\Concrete;

use App\Enums\Compensation as CompensationEnum;
use App\Enums\Formulable;
use App\Facades\Fractal;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Formula;
use App\Models\Payroll;
use App\Models\SalaryStatement;
use App\Transformers\SalaryStatementDetail\PipelineChainableTransformer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Pipeline;

class SalaryStatementModuleServiceConcrete
{
    public Employee $employee;

    public Collection $salaryStatementModules;

    public function __construct(
        protected Payroll $payroll,
        protected Company $company,
    ){
        $this->salaryStatementModules = $this->company->salaryStatementModules;
    }

    public function setEmployee(Employee $employee): static
    {
        $this->employee = $employee;

        return $this;
    }

    public function companyPerDayAbleEarningsMorphFilterSlugs(): array
    {
        /**
         * Company per day-able compensations: (Basic pay, Allowance, Overtime)
         **/
        $companyPerDayAbleEarnings = $this->company->compensations->where('assignable', true)
            ->where('formulable_type', Formulable::EARNINGS->value)
            ->whereIn('type', [CompensationEnum::BASIC_PAY, CompensationEnum::REGULAR_ALLOWANCE, CompensationEnum::OVERTIME]);

        $companyPerDayAbleEarningsMorphFilterSlugs = $companyPerDayAbleEarnings
            ->map(fn($companyPerDayEarning) => $companyPerDayEarning->id . '.compensation')
            ->values()
            ->toArray();

        return $companyPerDayAbleEarningsMorphFilterSlugs;
    }

    public function companyPerDayableGlobalCompensations(): Collection
    {
        /**
         * Company global compensations: (Leave pay, Holiday pay)
         **/
        return $this->company->compensations
            ->where('assignable', false)
            ->where('formulable_type', Formulable::EARNINGS->value)
            ->whereIn('type', [CompensationEnum::HOLIDAY_PAY, CompensationEnum::LEAVE_PAY])
            ->sortBy('order');
    }

    public function statementLevelPipeline(SalaryStatement $salaryStatement): void
    {
        $statementLevelModules = $this->salaryStatementModules->where('statement_level', true);

        $pipelinePayload = [];

        foreach ($statementLevelModules as $statementLevelModule) {

            if(
                empty($statementLevelModule->conditions) ||
                empty($statementLevelModule->property) ||
                empty($statementLevelModule->attribute)
            ) continue;

            $moduleProperty = clone $this->{$statementLevelModule->property};

            $moduleComponents = $moduleProperty->{$statementLevelModule->attribute};

            foreach($statementLevelModule->conditions as $condition)
            {
                $condition = (object)$condition;

                if ($condition->operator == '=') {
                    $moduleComponents = $moduleComponents->where($condition->property, $condition->value);
                }
            }

            switch($statementLevelModule->property)
            {
                case 'employee':

                    foreach($moduleComponents as $moduleComponent)
                    {
                        $payrollComponent = $moduleComponent->payrollComponentable;
                        $companyFormula = $payrollComponent->companyFormula;
                        $formula = $companyFormula->formula;

                        $pipelinePayload[] = [
                            'formulable_model' => $payrollComponent,
                            'formula' => $formula,
                            'formula_slug' => strtolower($formula->name)
                        ];
                    }

                    break;

                case 'company':

                    foreach($moduleComponents as $moduleComponent)
                    {
                        /**
                         * Aggregation formulas doesnt have formula settings
                         **/
                        if($moduleComponent instanceof Formula && $moduleComponent->aggregation){

                            $formula = $moduleComponent;

                            $pipelinePayload[] = [
                                'formulable_model' => null,
                                'formula' => $formula,
                                'formula_slug' => strtolower($formula->name)
                            ];
                        }

                    }

                    break;
            }
        }

        $pipelinePayload = collect($pipelinePayload);
        $pipeline = $pipelinePayload->map(fn($payload) => app($payload['formula_slug']))->values()->toArray();
        $statementDetails = Fractal::collection($salaryStatement->details, PipelineChainableTransformer::class)['data'];

        $pipelineContext = new SalaryStatementContext(
            $this->company,
            $this->payroll,
            $salaryStatement,
            $this->employee,
            $pipelinePayload,
            $statementDetails
        );

        array_unshift($pipeline, app('initialize-salary-statement'));

        $salaryStatementContext = Pipeline::send($pipelineContext)
            ->through($pipeline)
            ->thenReturn();

        foreach($salaryStatementContext->statementDetails as $statementDetail){

            _debug([
                'Statement details' => $statementDetail,
            ]);

            if(empty($statementDetail['id'])){

                $salaryStatement->details()->create($statementDetail);
            }
        }
    }
}
