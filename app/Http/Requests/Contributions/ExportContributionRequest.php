<?php

namespace App\Http\Requests\Contributions;

use App\Models\Hydrations\Contribution;
use Illuminate\Foundation\Http\FormRequest;

class ExportContributionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('export', Contribution::class);
    }
}
