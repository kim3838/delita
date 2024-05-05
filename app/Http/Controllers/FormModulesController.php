<?php

namespace App\Http\Controllers;

use App\Facades\Fractal;
use App\Facades\ResponseJson;
use Illuminate\Support\Facades\App;

class FormModulesController extends Controller
{
    public function selection($module)
    {
        if(request()->wantsJson()){
            return ResponseJson::successfulResponse([
                'selection' => Fractal::collection(
                    App::make($module)->selection(),
                    App::make('Transformer', [
                        'module' => $module,
                        'type' => 'selection'
                    ])
                )
            ]);
        }
    }
}
