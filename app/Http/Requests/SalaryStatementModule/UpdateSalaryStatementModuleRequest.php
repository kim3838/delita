<?php

namespace App\Http\Requests\SalaryStatementModule;

use App\Models\SalaryStatementModule;

class UpdateSalaryStatementModuleRequest extends BaseSalaryStatementModuleRequest
{
    public function authorize(): bool
    {
        $salaryStatementModule = SalaryStatementModule::findOrfail($this->route('salaryStatementModuleId'));

        return $this->user()->can('update', $salaryStatementModule);
    }
}
