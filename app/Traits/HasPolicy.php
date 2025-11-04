<?php

namespace App\Traits;

use Illuminate\Support\Facades\Gate;

trait HasPolicy
{
    protected function isActionAuthorized($action, $model): bool
    {
        return Gate::allows($action, $model);
    }
}
