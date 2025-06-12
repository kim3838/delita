<?php

namespace App\Policies;

use App\Models\PayPeriodSetting;
use App\Models\User;

class PayPeriodSettingPolicy
{
    public function update(User $user, PayPeriodSetting $payPeriodSetting): bool
    {
        return $user->isSuperAdmin();
    }
}
