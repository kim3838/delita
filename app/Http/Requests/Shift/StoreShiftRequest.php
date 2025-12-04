<?php

namespace App\Http\Requests\Shift;

use App\Models\Shift;
use Illuminate\Validation\Rule;

class StoreShiftRequest extends BaseShiftStoreAndUpdateRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Shift::class);
    }

    public function rules(): array
    {
        return array_merge([
            'code' => [
                'required',
                'string',
                'regex:/^\S+$/',//Must not contain any spaces, tabs, or line breaks
                'max:255',
                Rule::unique('shifts')->where(function ($query) {
                    return $query->where('company_id', $this->input('company_id'));
                })
            ],
        ], parent::rules());
    }

}
