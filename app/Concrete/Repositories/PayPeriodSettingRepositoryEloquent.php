<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\PayPeriodSettingRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\PayPeriodSetting;

class PayPeriodSettingRepositoryEloquent extends BaseRepositoryEloquent implements PayPeriodSettingRepository
{
    public function model(): string
    {
        return PayPeriodSetting::class;
    }
}
