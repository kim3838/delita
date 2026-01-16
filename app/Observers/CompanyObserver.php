<?php

namespace App\Observers;

use App\Events\Repositories\CompanyCreated;
use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CompanyObserver
{
    public function creating(Model $model): bool
    {
        if (empty($model->ulid)) {
            $model->ulid = (string) Str::ulid();
        }

        return true;
    }

    public function created(Company $company): void
    {
        event(new CompanyCreated($company, Auth::user()));
    }
}
