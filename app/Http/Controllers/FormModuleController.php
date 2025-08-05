<?php

namespace App\Http\Controllers;

use App\Facades\Fractal;
use App\Facades\ResponseJson;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Request;

class FormModuleController extends Controller
{
    public function selection($module)
    {
        if(request()->wantsJson()){

            $filters = json_decode(Request::get('filters'));

            return ResponseJson::successfulResponse([
                'selection' => Fractal::collection(
                    App::make($module)->selection($filters),
                    App::make('Transformer', [
                        'module' => $module,
                        'type' => 'selection'
                    ])
                )
            ]);
        }
    }
}
