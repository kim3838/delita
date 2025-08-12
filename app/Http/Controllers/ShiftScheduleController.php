<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\ShiftScheduleRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Transformers\ShiftSchedule\PatchableTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class ShiftScheduleController
{
    public function preset(Request $request)
    {
        if(request()->expectsJson()){

            $shiftSchedulesPreset = App::make(ShiftScheduleRepository::class)->preset($request->get('company_id'));

            $shiftSchedulesPreset = Fractal::collection($shiftSchedulesPreset, PatchableTransformer::class, 'shift_schedules_preset');

            return ResponseJson::successfulResponse($shiftSchedulesPreset);
        }

        abort(404);
    }
}
