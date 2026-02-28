<?php

namespace App\Concrete;

use App\Models\Company;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\SalaryStatement;
use Illuminate\Support\Collection;

class SalaryStatementContext
{
    public function __construct(
        public Company $company,
        public Payroll $payroll,
        public SalaryStatement $salaryStatement,
        public Collection $additionalSalaryStatements,
        public Employee $employee,
        public Collection $pipelinePayload,
        public array $flags = [],
        public array $statementDetails,
        public array $totals = []
    ){}
}
