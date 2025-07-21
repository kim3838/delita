<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\AssociatedUserRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Transformers\AssociatedUser\ListTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class AssociatedUserController extends Controller
{
    public function index(Request $request)
    {
        if($request->expectsJson()){

            return ResponseJson::successfulResponse(
                Fractal::collection(
                    App::make(AssociatedUserRepository::class)->list(),
                    ListTransformer::class
                )
            );
        }

        abort(404);
    }
}
