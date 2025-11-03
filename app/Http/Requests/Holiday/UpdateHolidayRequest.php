<?php

namespace App\Http\Requests\Holiday;

use App\Models\Holiday;

class UpdateHolidayRequest extends BaseStoreAndUpdateHolidayRequest
{
    public function authorize(): bool
    {
        $holiday = Holiday::query()->where('ulid', $this->route('holidayUlid'))->firstOrFail();

        return $this->user()->can('update', $holiday);
    }
}
