<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\AssociatedUserRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\User\ListUserRequest;
use App\Transformers\AssociatedUser\ListTransformer;
use Illuminate\Support\Facades\App;

class AssociatedUserController extends Controller
{
    public function indexGate(ListUserRequest $request)
    {
        if($request->expectsJson()){

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }

    public function index(ListUserRequest $request)
    {
        if($request->expectsJson()){

            $filters = json_decode($request->get('filters'));

            return ResponseJson::successfulResponse(
                Fractal::collection(
                    App::make(AssociatedUserRepository::class)->paginate($filters),
                    ListTransformer::class
                )
            );
        }

        abort(404);
    }
}
