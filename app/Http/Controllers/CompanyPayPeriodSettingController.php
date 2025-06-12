<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\CompanyRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Transformers\CompanyPayPeriodSetting\ItemTransformer as CompanyPayPeriodSettingTransformer;
use Illuminate\Support\Facades\App;
use stdClass;

class CompanyPayPeriodSettingController extends Controller
{
    public function index($companyId)
    {
        if(request()->expectsJson()){

            $payPeriodSetting = App::make(CompanyRepository::class)->show($companyId)->payPeriodSetting;

            return ResponseJson::successfulResponse([
                'pay_period_setting' => $payPeriodSetting
                    ? Fractal::item($payPeriodSetting, CompanyPayPeriodSettingTransformer::class)
                    : new StdClass()
            ]);
        }

        abort(404);
    }
}
