<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\TimePeriodPresetRepository;
use App\Enums\TimePeriodType;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Transformers\TimePeriodPreset\SelectionTransformer as PayPeriodPresetSelectionTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class TimePeriodPresetController extends Controller
{
    public function selection(Request $request)
    {
        if($request->expectsJson()){

            $filters = [
                'type' => TimePeriodType::PAY_FREQUENCY,
                'time_period_preset_names' => [
                    '05th_cut_off',
                    '20th_cut_off',
                    '25th_cut_off',
                    'end_of_month_cut_off',
                ]
            ];

            $request->query->set('filters', json_encode($filters));

            $filters = json_decode($request->get('filters'));

            return ResponseJson::successfulResponse(
                Fractal::collection(
                    App::make(TimePeriodPresetRepository::class)->selection($filters),
                    PayPeriodPresetSelectionTransformer::class,
                    'selection'
                )
            );
        }

        abort(404);
    }
}
