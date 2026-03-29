<?php

namespace App\Http\Requests\Tax;

use App\Models\Hydrations\Tax;
use Illuminate\Foundation\Http\FormRequest;

class ListTaxRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', Tax::class);
    }
}
