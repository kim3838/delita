<?php

namespace App\Concrete;

use App\Blueprint\EmployeeServiceInterface;
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

    public function chainNontaxable(
        BigDecimal $subject,
        BigDecimal $addChain,
        &$nontaxableReference,
        &$taxableReference,
        BigDecimal $taxExempt,
        $updateReference = true
    ): array{

        $chain = $subject->plus($addChain);
        $subjectNontaxable = BigDecimal::zero();
        $subjectTaxable = BigDecimal::zero();

        _debug([
            'Subject' => $subject->toString(),
            'Add chain' => $addChain->toString(),
            'Chain' => $chain->toString(),
            'Chain over 90,000' => $chain->isGreaterThan($taxExempt) ? 'Yes' : 'No'
        ]);

        if($chain->isGreaterThan($taxExempt)){

            $subjectTaxable = $chain->minus($taxExempt);

            $subjectNontaxable = $subject->minus($subjectTaxable);

            _debug([
                'Subject taxable' => $subjectTaxable->toString(),
                'Subject nontaxable' => $subjectNontaxable->toString(),
            ]);

            if($updateReference){
                $taxableReference = $taxableReference->plus($subjectTaxable);
                $nontaxableReference = $nontaxableReference->plus($subjectNontaxable);
            }

        } else {

            $subjectNontaxable = $subject;

            _debug([
                'Subject nontaxable' => $subjectNontaxable->toString(),
            ]);

            if($updateReference){
                $nontaxableReference = $nontaxableReference->plus($subjectNontaxable);
            }
        }

        _debug([
            'Next chain' => $addChain->plus($subjectNontaxable)->toString(),
        ]);

        return [
            $addChain->plus($subjectNontaxable),
            $subjectNontaxable,
            $subjectTaxable,
        ];
    }
}
