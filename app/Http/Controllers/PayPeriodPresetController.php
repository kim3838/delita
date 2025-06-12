<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\TimePeriodPresetRepository;
use App\Enums\TimePeriodType;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Transformers\PayPeriodPreset\SelectionTransformer as PayPeriodPresetSelectionTransformer;
use Illuminate\Support\Facades\App;

class PayPeriodPresetController extends Controller
{
    public function selection()
    {
        if(request()->expectsJson()){

            $filters = [
                'type' => TimePeriodType::PAY_PERIOD
            ];

            request()->query->set('filters', json_encode($filters));

            return ResponseJson::successfulResponse(
                Fractal::collection(
                    App::make(TimePeriodPresetRepository::class)->selection(),
                    PayPeriodPresetSelectionTransformer::class,
                    'selection'
                )
            );
        }

        abort(404);
    }
}
