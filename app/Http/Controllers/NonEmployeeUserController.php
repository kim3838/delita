<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\NonEmployeeUserRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Transformers\NonEmployeeUser\SelectionTransformer;
use Illuminate\Support\Facades\App;

class NonEmployeeUserController extends Controller
{
    public function selection()
    {
        if(request()->expectsJson()){

            return ResponseJson::successfulResponse(
                Fractal::collection(
                    App::make(NonEmployeeUserRepository::class)->selection(),
                    SelectionTransformer::class,
                    'selection'
                )
            );
        }

        abort(404);
    }
}
