<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class HasUlid
{
    public function creating(Model $model)
    {
        if (empty($model->ulid)) {
            $model->ulid = (string) Str::ulid();
        }

        return true;
    }
}
