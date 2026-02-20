<?php

namespace App\Http\Requests\SalaryStatement;

use App\Models\SalaryStatement;
use Illuminate\Foundation\Http\FormRequest;

class ViewSalaryStatementRequest extends FormRequest
{
    public function authorize(): bool
    {
        $salaryStatement = SalaryStatement::query()->where('ulid', $this->route('ulid'))->firstOrFail();

        return $this->user()->can('view', $salaryStatement);
    }
}
