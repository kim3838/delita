<?php

namespace App\Concrete;

use App\Enums\Compensation as CompensationEnum;
use App\Enums\Formulable;
use App\Enums\PayFrequency;
use App\Enums\SemiMonthlySequence;
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
        $this->salaryStatementModules = $this->company->salaryStatementModules->sortBy('order');
    }

    public function setEmployee(Employee $employee): static
    {
        $this->employee = $employee;

        return $this;
    }

    public function companyPerDayAbleEarningsComponentSubTypeFilterSlugs(
        $compensationEnums = [CompensationEnum::BASIC_PAY, CompensationEnum::REGULAR_ALLOWANCE, CompensationEnum::OVERTIME]
    ): array{

        /**
         * Company per day-able compensations: (Basic pay, Allowance, Overtime)
         **/
        $companyPerDayAbleEarnings = $this->company->compensations->where('assignable', true)
            ->where('formulable_type', Formulable::EARNINGS->value)
            ->whereIn('type', $compensationEnums);

        $companyPerDayAbleEarningsComponentSubTypeSlugs = $companyPerDayAbleEarnings
            ->map(fn($companyPerDayEarning) => $companyPerDayEarning->component_sub_type->value)
            ->values()
            ->toArray();

        return $companyPerDayAbleEarningsComponentSubTypeSlugs;
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

    public function processPipelineOfFormulasAndUpdateStatementSummary(SalaryStatement $salaryStatement, $rebuildStatementLevel = false, $manualSalaryStatementItems = []): void
    {
        $debugEnabled = true;

        /**
         * Rebuilding statement as isolated function only deletes statement details and not the totals from salary statement
         * Requiring the exception of the currents period so it won't duplicate
         **/
        if($rebuildStatementLevel){$salaryStatement->details()->where('statement_level', '=', 1)->delete();}

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

            $conditionsArray = collect($statementLevelModule->conditions)->sortBy('order')->toArray();

            foreach($conditionsArray as $condition)
            {
                $condition = (object)$condition;

                if ($condition->operator == '=') {

                    if(is_array($condition->property)){

                        $path = implode('.', $condition->property);

                        $moduleComponents = $moduleComponents->where(fn($component) => data_get($component, $path) == $condition->value);

                    } else {

                        $moduleComponents = $moduleComponents->where($condition->property, $condition->value);
                    }
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
                        if($moduleComponent instanceof Formula){

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

        $additionalSalaryStatements = collect();
        $pipelinePayload = collect($pipelinePayload);
        $pipeline = $pipelinePayload->map(fn($payload) => app($payload['formula_slug']))->values()->toArray();
        $statementDetails = Fractal::collection($salaryStatement->details, PipelineChainableTransformer::class)['data'];

        $isWeekly = $salaryStatement->payroll->pay_frequency == PayFrequency::WEEKLY;
        $isWeeklyAndIsLastSplitOfMonth = false;

        if($isWeekly){

            $weekSpan = 7;
            $nextWeeklyPayrollEndDate = $salaryStatement->payroll->end_date->copy()->addDays($weekSpan);

            $nextWeeklyPayrollIsSameYearMonthAsIntermediate = $nextWeeklyPayrollEndDate->year == $salaryStatement->payroll->end_date->year &&
                $nextWeeklyPayrollEndDate->month == $salaryStatement->payroll->end_date->month;

            $isWeeklyAndIsLastSplitOfMonth = !$nextWeeklyPayrollIsSameYearMonthAsIntermediate;
        }

        $flags = [
            'is_monthly' => $salaryStatement->payroll->pay_frequency == PayFrequency::MONTHLY,
            'is_semimonthly_and_is_1st_half' => $salaryStatement->payroll->pay_frequency == PayFrequency::SEMIMONTHLY &&
                $salaryStatement->payroll->frequency_sequence == SemiMonthlySequence::FIRST_HALF,
            'is_semimonthly_and_is_2nd_half' => $salaryStatement->payroll->pay_frequency == PayFrequency::SEMIMONTHLY &&
                $salaryStatement->payroll->frequency_sequence == SemiMonthlySequence::SECOND_HALF,
            'is_weekly_and_is_last_split_of_month' => $isWeeklyAndIsLastSplitOfMonth,
            'rebuild_statement_level' => $rebuildStatementLevel,
        ];

        $pipelineContext = new SalaryStatementContext(
            $salaryStatement->payroll->company,
            $salaryStatement->payroll,
            $salaryStatement,
            $additionalSalaryStatements,
            $salaryStatement->employee,
            $pipelinePayload,
            $flags,
            $statementDetails,
            $manualSalaryStatementItems
        );

        array_unshift($pipeline, app('initialize-salary-statement'));

        if($debugEnabled){
            _debug([
                'Pre-pipeline' => [
                    'Pipeline' => array_map(fn($pipelineItem) => get_class($pipelineItem), $pipeline),
                    'Should be non statement level salary statement details' => count($statementDetails),
                ]
            ]);
        }

        $salaryStatementContext = Pipeline::send($pipelineContext)
            ->through($pipeline)
            ->thenReturn();

        foreach($salaryStatementContext->statementDetails as $statementDetail){

            if(empty($statementDetail['id'])){

                $salaryStatement->details()->create($statementDetail);
            }
        }

        if($debugEnabled){
            _debug([
                'Salary statement totals' => $salaryStatementContext->totals
            ]);
        }

        $salaryStatement->update($salaryStatementContext->totals);
    }
}
