<?php

namespace App\Http\Requests\Deduction;

use App\Models\Deduction;
use Illuminate\Foundation\Http\FormRequest;

class DestroyDeductionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $deduction = Deduction::findOrfail($this->route('deductionId'));

        return $this->user()->can('delete', $deduction);
    }
}
