<?php

namespace App\Http\Requests\Holiday;

use App\Models\Holiday;
use Illuminate\Validation\Rule;

class UpdateHolidayRequest extends BaseStoreAndUpdateHolidayRequest
{
    public function authorize(): bool
    {
        $holiday = Holiday::query()->where('ulid', $this->route('holidayUlid'))->firstOrFail();

        return $this->user()->can('update', $holiday);
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'date' => [
                'required',
                'date_format:Y-m-d',
                Rule::unique('holidays')->where(function ($query) {
                    return $query->where('company_id', $this->input('company_id'))
                        ->where('ulid', '!=', $this->route('holidayUlid'));
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
