<?php

namespace App\Http\Requests\SalaryStatement;

use App\Models\SalaryStatement;
use Illuminate\Foundation\Http\FormRequest;

class ListSalaryStatementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', SalaryStatement::class);
    }
}
