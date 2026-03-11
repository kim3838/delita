<?php

namespace App\Transformers\User;

use App\Models\User;
use League\Fractal\TransformerAbstract;

class PatchableTransformer extends TransformerAbstract
{
    public function transform(User $model): array
    {
        return [
            'id' => $model->id,
            'ulid' => $model->ulid,
            'username' => $model->name,
            'email' => $model->email,
            'status' => $model->status?->toArray(),
            'email_verified_at' => $model->email_verified_at?->format('Y-m-d'),
            'timezone' => $model->timezone,
            'employable' => $model->employable,
            'roles' => $model->roles->pluck('id')->values()?->toArray(),
        ];
    }
}
