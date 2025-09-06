<?php

namespace App\Http\Requests\SalaryStatementModule;

use App\Models\SalaryStatementModule;

class StoreSalaryStatementModuleRequest extends BaseSalaryStatementModuleRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', SalaryStatementModule::class);
    }
}
