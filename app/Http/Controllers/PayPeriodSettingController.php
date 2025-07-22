<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\PayPeriodSettingRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\PayPeriodSetting\StorePayPeriodSettingRequest;
use App\Http\Requests\PayPeriodSetting\UpdatePayPeriodSettingRequest;
use App\Transformers\PayPeriodSetting\ItemTransformer as PayPeriodSettingItemTransformer;
use Illuminate\Support\Facades\App;

class PayPeriodSettingController extends Controller
{
    public function store(StorePayPeriodSettingRequest $request)
    {
        if($request->expectsJson()){

            return ResponseJson::successfulResponse([
                'pay_period_setting' => Fractal::item(
                    App::make(PayPeriodSettingRepository::class)->store($request->validated()),
                    PayPeriodSettingItemTransformer::class
                ),
            ]);
        }

        abort(404);
    }

    public function update(UpdatePayPeriodSettingRequest $request, $payPeriodSettingId)
    {
        if($request->expectsJson()){

            return ResponseJson::successfulResponse([
                'pay_period_setting' => Fractal::item(
                    App::make(PayPeriodSettingRepository::class)->update($payPeriodSettingId, $request->validated()),
                    PayPeriodSettingItemTransformer::class
                ),
            ]);
        }

        abort(404);
    }
}
