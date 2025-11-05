<?php

namespace App\Http\Requests\Holiday;

use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

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
            'effective_date' => 'required|date_format:Y-m-d|after_or_equal:date',
        ];
    }

    public function validateReccuring($attribute, $value, $fail, $ulid = null): void
    {
        $date = Carbon::parse($this->input('date'));
        $recurring = $this->input('recurring');
        $companyId = $this->input('company_id');

        /**
         * If the holiday is recurring
         * Check if there's already a recurring holiday with the same month and day
         * and if there's already a non-recurring that is greater than the holiday date with the same month and day
         *
         * If the holiday is non-recurring
         * Check if there's already a recurring holiday that is lesser than the holiday date with the same month and day
         * and if there's already a non-recurring holiday with the same month and day
         **/
        $existingHoliday = Holiday::getQuery()
            ->where('company_id', $companyId)
            ->when($ulid, function ($query, $ulid) {
                $query->where('ulid', '!=', $ulid);
            });

        $existingHoliday
            ->when($recurring, function ($query) use ($date) {
                $query->where(function ($query) use ($date) {
                    $query->where('recurring', 0)
                        ->where(DB::raw("`date`"), ">=", $date->format('Y-m-d'))
                        ->whereMonth('date', $date->month)
                        ->whereDay('date', $date->day);
                })->orWhere(function ($query) use ($date) {
                    $query->where('recurring', 1)
                        ->whereMonth('date', $date->month)
                        ->whereDay('date', $date->day);
                });
            })
            ->when(!$recurring, function ($query) use ($date) {
                $query->where(function ($query) use ($date) {
                    $query->where('recurring', 0)
                        ->where('date', $date->format('Y-m-d'));
                })->orWhere(function ($query) use ($date) {
                    $query->where('recurring', 1)
                        ->where('date', '<=', $date->format('Y-m-d'))
                        ->whereMonth('date', $date->month)
                        ->whereDay('date', $date->day);
                });

            });

        $existingHoliday = !empty($existingHoliday->first())
            ? Holiday::hydrate([$existingHoliday->first()])->first()
            : null;

        if (!empty($existingHoliday)) {
            $fail('A '. ($existingHoliday->recurring ? 'recurring' : 'non-recurring') .' holiday already exists for ' . $date->format('F jS'));
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
            'effective_date.date_format' => 'Effective date must match the format Y-m-d e.g.(2000-12-31)',
            'effective_date.after_or_equal' => 'Effective date must be equal to or after the holiday date',
        ];
    }
}
