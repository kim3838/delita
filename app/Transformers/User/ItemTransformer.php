<?php

namespace App\Transformers\User;

use App\Facades\TimeZoneConverterFacade;
use App\Models\User;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class ItemTransformer extends TransformerAbstract
{
    public function transform(User $model)
    {
        return [
            'id' => $model->id,
            'ulid' => $model->ulid,
            'username' => $model->name,
            'email' => $model->email,
            'status' => $model->status->toArray(),
            'email_verified_at' => TimeZoneConverterFacade::globalToLocal($model->email_verified_at),
            'timezone' => $model->timezone,
        ];
    }
}
