<?php

namespace App\Http\Requests\SalaryStatementModule;

use App\Models\SalaryStatementModule;
use Illuminate\Foundation\Http\FormRequest;

class DestroySalaryStatementModuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $salaryStatementModule = SalaryStatementModule::query()->findOrfail($this->route('salaryStatementModuleId'));

        return $this->user()->can('delete', $salaryStatementModule);
    }
}
