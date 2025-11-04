<?php

namespace App\Http\Requests\Holiday;

use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class BaseStoreAndUpdateHolidayRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'company_id' => 'required|numeric|integer',
            'active' => 'required|boolean',
            'name' => 'required|string|max:255',
            'type' => 'required|numeric|integer',
            'recurring' => [
                'required',
                'boolean',
                function ($attribute, $value, $fail) {
                    $this->validateReccuring($attribute, $value, $fail, $this->route('holidayUlid'));
                }
            ],
            'effective_date' => 'required|date_format:Y-m-d',
        ];
    }

    public function validateReccuring($attribute, $value, $fail, $ulid = null): void
    {
        $date = Carbon::parse($this->input('date'));
        $companyId = $this->input('company_id');

        /**
         * If holiday is recurring
         * Check if there's already a holiday with the same month and day
         **/
        $existingHoliday = Holiday::query()
            ->where('company_id', $companyId)
            ->when($ulid, function ($query, $ulid) {
                $query->where('ulid', '!=', $ulid);
            })
            ->where('recurring', true)
            ->whereMonth('date', $date->month)
            ->whereDay('date', $date->day)
            ->first();

        if ($existingHoliday) {
            $fail('A recurring holiday already exists for ' . $date->format('F jS'));
        }
    }

    public function messages(): array
    {
        return [
            'company_id.required' => 'Company is required',
            'name.required' => 'Holiday name is required',
            'type.required' => 'Holiday type is required',
            'date.required' => 'Date is required',
            'recurring.required' => 'Recurring is required',
            'active.required' => 'Active is required',
            'effective_date.required' => 'Effective date is required',
        ];
    }
}
