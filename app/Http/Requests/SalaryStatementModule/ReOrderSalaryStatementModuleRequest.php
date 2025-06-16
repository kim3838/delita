<?php

namespace App\Http\Requests\SalaryStatementModule;

use App\Models\SalaryStatementModule;
use Illuminate\Foundation\Http\FormRequest;

class ReOrderSalaryStatementModuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('reOrder', SalaryStatementModule::class);
    }
}
