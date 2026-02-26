<?php

namespace App\Observers;

use App\Events\Repositories\UserCreated;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class UserObserver
{
    public function creating(Model $model): bool
    {
        if (empty($model->ulid)) {
            $model->ulid = (string) Str::ulid();
        }

        return true;
    }

    public function created(User $user): void
    {
        event(new UserCreated($user));
    }
}
