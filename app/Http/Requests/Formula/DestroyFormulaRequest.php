<?php

namespace App\Http\Requests\Formula;

use App\Models\Formula;
use Illuminate\Foundation\Http\FormRequest;

class DestroyFormulaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $formula = Formula::findOrFail($this->route('formulaId'));

        return $this->user()->can('delete', $formula);
    }
}
