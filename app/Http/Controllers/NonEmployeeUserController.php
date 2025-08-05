<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\NonEmployeeUserRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Transformers\NonEmployeeUser\SelectionTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class NonEmployeeUserController extends Controller
{
    public function selection(Request $request)
    {
        if(request()->expectsJson()){

            $filters = json_decode($request->get('filters'));

            return ResponseJson::successfulResponse(
                Fractal::collection(
                    App::make(NonEmployeeUserRepository::class)->selection($filters),
                    SelectionTransformer::class,
                    'selection'
                )
            );
        }

        abort(404);
    }
}
