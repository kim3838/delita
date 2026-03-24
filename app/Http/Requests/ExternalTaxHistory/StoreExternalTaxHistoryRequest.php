<?php

namespace App\Http\Requests\ExternalTaxHistory;

use App\Models\ExternalTaxHistory;

class StoreExternalTaxHistoryRequest extends BaseExternalTaxHistoryStoreAndUpdateRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', ExternalTaxHistory::class);
    }
}
