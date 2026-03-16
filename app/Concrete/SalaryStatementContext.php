<?php

namespace App\Concrete;

use App\Blueprint\EmployeeServiceInterface;
use App\Blueprint\Repositories\SalaryStatementRepository;
use App\Enums\SalaryStatementType;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\SalaryStatement;
use Brick\Math\BigDecimal;
use Illuminate\Support\Collection;

class SalaryStatementContext
{
    public EmployeeServiceInterface $employeeService;

    public bool $isFinalPayState = false;
    public bool $isPayrollYearEnd = false;

    public function __construct(
        public Company $company,
        public Payroll $payroll,
        public SalaryStatement $salaryStatement,
        public Collection $additionalSalaryStatements,
        public Employee $employee,
        public Collection $pipelinePayload,
        public array $flags = [],
        public array $statementDetails,
        public array $manualSalaryStatementItems = [],
        public array $totals = [],
        public SalaryStatementType $type = SalaryStatementType::DEFAULT
    ){
        $this->employeeService = app(EmployeeServiceInterface::class, [$this->employee]);

        list($isYearEnd, $noUpcomingEmployment) = $this->employeeService->getPayrollAndEmploymentPayload($this->payroll);

        $this->isFinalPayState = $noUpcomingEmployment;
        $this->isPayrollYearEnd = $isYearEnd;

        if($this->isFinalPayState){

            $this->type = SalaryStatementType::FINAL_PAY;
        }

        $this->salaryStatement->update([
            'type' => $this->type->value,
        ]);
    }

    public function getPayrollYearSalaryStatements(?Payroll $payroll, ?Employee $employee, ?SalaryStatement $salaryStatement = null, $rebuildStatementLevel = false): Collection
    {
        $payroll = $payroll ?? $this->payroll;
        $employee = $employee ?? $this->employee;

        $payrollMonthRange = [
            'payroll_from_month' => $payroll->year . '-01',
            'payroll_to_month' => $payroll->year . '-' . str_pad($payroll->month, 2, '0', STR_PAD_LEFT)
        ];

        $calendarYearSalaryStatementFilters = (object)[
            'company_ids' => [$payroll->company->id],
            'payroll_year' => $payroll->year,
            ...$payrollMonthRange,
            'employee_ids' => [$employee->id],

            /**
             * Exclude current periods statement if rebuild statement level is enabled,
             * Rebuilding statement as isolated function only deletes statement details and not the totals from salary statement
             * Requiring the exception of the currents period so it won't duplicate
             **/
            ...($rebuildStatementLevel && !empty($salaryStatement) ? [
                'not_salary_statement_ids' => [$salaryStatement->id]
            ] : [])
        ];

        $calendarYearSalaryStatements = app(SalaryStatementRepository::class)
            ->list($calendarYearSalaryStatementFilters, ['no_day_totals', 'payroll', 'detail_totals']);

        return $calendarYearSalaryStatements;
    }

    public function getTotalFromSalaryStatementCollection(Collection $salaryStatements, string $key): BigDecimal
    {
        $total = BigDecimal::zero();

        foreach($salaryStatements as $salaryStatement){

            $total = $total->plus(BigDecimal::of($salaryStatement->{$key}));
        }

        return $total;
    }

    public function chainNontaxable(
        BigDecimal $subject,
        BigDecimal $addChain,
        &$nontaxableReference,
        &$taxableReference,
        BigDecimal $taxExempt,
        $updateReference = true
    ): array{

        $debugEnabled = false;

        $chain = $subject->plus($addChain);
        $subjectNontaxable = BigDecimal::zero();
        $subjectTaxable = BigDecimal::zero();

        if($debugEnabled){

            _debug([
                'Subject' => $subject->toString(),
                'Add chain' => $addChain->toString(),
                'Chain' => $chain->toString(),
                'Chain over 90,000' => $chain->isGreaterThan($taxExempt) ? 'Yes' : 'No'
            ]);
        }


        if($chain->isGreaterThan($taxExempt)){

            $subjectTaxable = $chain->minus($taxExempt);

            $subjectNontaxable = $subject->minus($subjectTaxable);

            if($debugEnabled){

                _debug([
                    'Subject taxable' => $subjectTaxable->toString(),
                    'Subject nontaxable' => $subjectNontaxable->toString(),
                ]);
            }


            if($updateReference){
                $taxableReference = $taxableReference->plus($subjectTaxable);
                $nontaxableReference = $nontaxableReference->plus($subjectNontaxable);
            }

        } else {

            $subjectNontaxable = $subject;

            if($debugEnabled){

                _debug([
                    'Subject nontaxable' => $subjectNontaxable->toString(),
                ]);

            }

            if($updateReference){
                $nontaxableReference = $nontaxableReference->plus($subjectNontaxable);
            }
        }

        if($debugEnabled){

            _debug([
                'Next chain' => $addChain->plus($subjectNontaxable)->toString(),
            ]);

        }

        return [
            $addChain->plus($subjectNontaxable),
            $subjectNontaxable,
            $subjectTaxable,
        ];
    }
}
