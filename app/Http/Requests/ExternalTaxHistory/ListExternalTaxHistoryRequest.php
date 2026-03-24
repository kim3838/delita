<?php

namespace App\Http\Requests\ExternalTaxHistory;

use App\Models\ExternalTaxHistory;
use Illuminate\Foundation\Http\FormRequest;

class ListExternalTaxHistoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', ExternalTaxHistory::class);
    }
}
