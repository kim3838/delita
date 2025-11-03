<?php

namespace App\Http\Requests\Holiday;

use App\Models\Holiday;

class StoreHolidayRequest extends BaseStoreAndUpdateHolidayRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Holiday::class);
    }
}
