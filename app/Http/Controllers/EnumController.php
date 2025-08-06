<?php

namespace App\Http\Controllers;

use App\Blueprint\EnumInterface;
use App\Facades\ResponseJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;

class EnumController extends Controller
{
    public function selection($enum): JsonResponse
    {
        $enum = App::make(EnumInterface::class)->selection($enum);

        return ResponseJson::successfulResponse([
            'data' => $enum::all()
        ]);
    }

    public function payrollComponentPaySelections(): JsonResponse
    {
        $payPeriod = App::make(EnumInterface::class)->selection('pay_period');
        $payType = App::make(EnumInterface::class)->selection('pay_type');
        $payFrequency = App::make(EnumInterface::class)->selection('pay_frequency');

        return ResponseJson::successfulResponse([
            'pay_period' => $payPeriod::all(),
            'pay_type' => $payType::all(),
            'pay_frequency' => $payFrequency::all()
        ]);
    }
}
