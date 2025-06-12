<?php

namespace App\Transformers\CompanyPayPeriodSetting;

use App\Models\PayPeriodSetting;
use League\Fractal\TransformerAbstract;

class ItemTransformer extends TransformerAbstract
{
    public function transform(PayPeriodSetting $model): array
    {
        return [
            'id' => (int)$model->id,
            'company_id' => $model->company_id,
            'days_to_pay_after_cut_off' => $model->days_to_pay_after_cut_off,
            'time_period_preset_reference' => $model->time_period_preset_reference,
            'monthly_pay_period' => $model->monthly_pay_period->cast,
            'semimonthly_pay_period' => $model->semimonthly_pay_period->cast,
        ];
    }
}
