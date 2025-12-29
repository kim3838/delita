<?php

namespace App\Http\Requests\Shift;

use App\Enums\RegexValidation;
use App\Models\Shift;
use Illuminate\Validation\Rule;

class UpdateShiftRequest extends BaseShiftStoreAndUpdateRequest
{

    public function authorize(): bool
    {
        $shift = Shift::query()->findOrFail($this->route('shiftId'));

        return $this->user()->can('update', $shift);
    }

    public function rules(): array
    {
        return array_merge([
            'code' => [
                'required',
                'string',
                'regex:' . RegexValidation::NO_WHITESPACE->value,
                'max:255',
                Rule::unique('shifts')->where(function ($query) {
                    return $query->where('company_id', $this->input('company_id'))
                        ->whereNot('id', $this->route('shiftId'));
                })
            ],
            'shift_schedules.*.id' => ['required', 'integer'],
            'shift_schedules.*.shift_id' => ['required', 'integer'],
        ], parent::rules());
    }
}
