<?php

namespace App\Http\Requests\Formula;

use App\Models\Formula;
use Illuminate\Foundation\Http\FormRequest;

class ViewFormulaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $formula = Formula::where('ulid', $this->route('ulid'))->firstOrFail();

        return $this->user()->can('view', $formula);
    }
}
