<?php

namespace App\Http\Requests\Contributions;

use App\Models\Hydrations\Contribution;
use Illuminate\Foundation\Http\FormRequest;

class ListContributionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', Contribution::class);
    }
}
