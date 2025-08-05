<?php

namespace App\Http\Controllers;

use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Models\Company;
use App\Transformers\CompanyPayPeriodSetting\ItemTransformer as CompanyPayPeriodSettingTransformer;

class CompanyPayPeriodSettingController extends Controller
{
    public function index($companyId)
    {
        if(request()->expectsJson()){

            $payPeriodSetting = Company::find($companyId)?->payPeriodSetting;

            return ResponseJson::successfulResponse([
                'pay_period_setting' => $payPeriodSetting
                    ? Fractal::item($payPeriodSetting, CompanyPayPeriodSettingTransformer::class)
                    : [
                        'company_id' => $companyId,
                        'days_to_pay_after_cut_off' => 0
                    ]
            ]);
        }

        abort(404);
    }
}
