<?php

namespace App\Concrete;

use App\Blueprint\EmployeeServiceInterface;
use App\Enums\SalaryStatementType;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\SalaryStatement;
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
}
