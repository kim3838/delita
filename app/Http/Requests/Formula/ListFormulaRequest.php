<?php

namespace App\Http\Requests\Formula;

use App\Models\Formula;
use Illuminate\Foundation\Http\FormRequest;

class ListFormulaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', Formula::class);
    }
}
