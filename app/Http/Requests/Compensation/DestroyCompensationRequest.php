<?php

namespace App\Http\Requests\Compensation;

use App\Models\Compensation;
use Illuminate\Foundation\Http\FormRequest;

class DestroyCompensationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $compensation = Compensation::query()->findOrfail($this->route('compensationId'));

        return $this->user()->can('delete', $compensation);
    }
}
