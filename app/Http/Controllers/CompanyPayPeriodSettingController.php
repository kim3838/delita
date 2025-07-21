<?php

namespace App\Http\Controllers;

use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Models\Company;
use App\Transformers\CompanyPayPeriodSetting\ItemTransformer as CompanyPayPeriodSettingTransformer;
use Illuminate\Support\Facades\App;
use stdClass;

class CompanyPayPeriodSettingController extends Controller
{
    public function index($companyId)
    {
        if(request()->expectsJson()){

            $payPeriodSetting = Company::find($companyId)?->payPeriodSetting;

            return ResponseJson::successfulResponse([
                'pay_period_setting' => $payPeriodSetting
                    ? Fractal::item($payPeriodSetting, CompanyPayPeriodSettingTransformer::class)
                    : new StdClass()
            ]);
        }

        abort(404);
    }
}
