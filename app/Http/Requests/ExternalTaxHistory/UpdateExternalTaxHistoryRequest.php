<?php

namespace App\Http\Requests\ExternalTaxHistory;

use App\Models\ExternalTaxHistory;

class UpdateExternalTaxHistoryRequest extends BaseExternalTaxHistoryStoreAndUpdateRequest
{
    public function authorize(): bool
    {
        $externalTaxHistory = ExternalTaxHistory::query()->where('ulid', $this->route('externalTaxHistoryUlid'))->firstOrFail();

        return $this->user()->can('update', $externalTaxHistory);
    }
}
