<?php

namespace App\Transformers\Company;

use App\Facades\Fractal;
use App\Models\Company;
use App\Transformers\Account\BasicTransformer as AccountBasicTransformer;
use League\Fractal\TransformerAbstract;

class SelectionTransformer extends TransformerAbstract
{
    public function transform(Company $model): array
    {
        $company = Company::query()->find($model->id);
        $account = Fractal::item($company->account, AccountBasicTransformer::class);

        return [
            'value' => $model->id,
            'text' => $model->short_name,
            'payload' => [
                'ulid' => $model->ulid,
                'currency' => $model->currency,
                'timezone' => $model->timezone,
                'account' => $account,
            ]
        ];
    }
}
