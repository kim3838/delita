<?php

namespace App\Http\Requests\Holiday;

use App\Models\Holiday;
use Illuminate\Validation\Rule;

class StoreHolidayRequest extends BaseStoreAndUpdateHolidayRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Holiday::class);
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'date' => [
                'required',
                'date_format:Y-m-d',
                Rule::unique('holidays')->where(function ($query) {
                    return $query->where('company_id', $this->input('company_id'));
                })
            ],
        ]);
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'date.unique' => 'Date has already been taken',
        ]);
    }
}
