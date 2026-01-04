<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\AccountSubscriptionRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\AccountSubscription;

class AccountSubscriptionRepositoryEloquent extends BaseRepositoryEloquent implements AccountSubscriptionRepository
{
    public function model(): string
    {
        return AccountSubscription::class;
    }
}
