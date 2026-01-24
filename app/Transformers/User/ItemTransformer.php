<?php

namespace App\Transformers\User;

use App\Models\User;
use League\Fractal\TransformerAbstract;

class ItemTransformer extends TransformerAbstract
{
    public function transform(User $model): array
    {
        return [
            'id' => $model->id,
            'ulid' => $model->ulid,
            'username' => $model->name,
            'email' => $model->email,
            'status' => $model->status?->toArray(),
            'email_verified_at' => $model->email_verified_at?->toDateTimeString(),
            'email_verified' => !empty($model->email_verified_at),
            'timezone' => $model->timezone,
            'roles' => $model->roles
        ];
    }
}
