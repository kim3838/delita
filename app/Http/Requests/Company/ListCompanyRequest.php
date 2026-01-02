<?php

namespace App\Http\Requests\Company;

use App\Models\Company;
use Illuminate\Foundation\Http\FormRequest;

class ListCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', Company::class);
    }
}
