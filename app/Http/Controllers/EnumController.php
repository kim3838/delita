<?php

namespace App\Http\Controllers;

use App\Blueprint\EnumInterface;
use App\Enums\Compensation as CompensationEnum;
use App\Facades\ResponseJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;

class EnumController extends Controller
{
    public function selection($enum): JsonResponse
    {
        $selection = [];
        $filters = json_decode(request()->get('filters'));

        switch($enum){
            case 'compensation':

                $assignableOnly = $filters->assignable_only ?? true;

                $compensations = App::make(EnumInterface::class)->selection('compensation');

                if($assignableOnly){
                    $globalCompensations = [CompensationEnum::LEAVE_PAY->value, CompensationEnum::HOLIDAY_PAY->value];

                    $selection = collect($compensations::all())->filter(function($compensation) use($globalCompensations){
                        return !in_array($compensation['value'], $globalCompensations);
                    })->values()->toArray();
                } else {
                    $selection = $compensations::all();
                }


                break;

            default: $selection = App::make(EnumInterface::class)->selection($enum)::all();
        }

        return ResponseJson::successfulResponse([
            'data' => $selection,
            'filters' => $filters,
        ]);
    }

    public function payrollComponentPaySelections(): JsonResponse
    {
        $payPeriod = App::make(EnumInterface::class)->selection('pay_period');
        $payType = App::make(EnumInterface::class)->selection('pay_type');

        return ResponseJson::successfulResponse([
            'pay_period' => $payPeriod::all(),
            'pay_type' => $payType::all(),
        ]);
    }
}
