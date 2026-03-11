<?php

namespace App\Transformers\User;

use App\Models\User;
use League\Fractal\TransformerAbstract;

class BasicTransformer extends TransformerAbstract
{
    public function transform(User $model): array
    {
        $emailVerified = !empty($model->email_verified_at);
        $emailVerifiedReadable = empty($model->id) ? '' : ($emailVerified ? 'Verified' : 'Not verified');

        return [
            'username' => $model->name,
            'email' => $model->email,
            'status' => $model->status?->toArray(),
            'email_verified_at' => $model->email_verified_at?->toDateTimeString(),
            'email_verified' => $emailVerified,
            'email_verified_readable' => $emailVerifiedReadable,
            'timezone' => $model->timezone,
            'employable' => $model->employable,
        ];
    }
}
