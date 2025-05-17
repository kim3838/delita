<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\FormulaRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Transformers\Formula\SelectionTransformer;
use Illuminate\Support\Facades\App;

class FormulaController extends Controller
{
    public function selection()
    {
        if(request()->expectsJson()){

            return ResponseJson::successfulResponse(
                Fractal::collection(
                    App::make(FormulaRepository::class)->selection(),
                    SelectionTransformer::class,
                    'selection'
                )
            );
        }

        abort(404);
    }
}
