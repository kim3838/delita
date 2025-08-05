<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\AssociatedAccountRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Transformers\AssociatedAccount\ListTransformer;
use App\Transformers\AssociatedAccount\SelectionTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class AssociatedAccountController extends Controller
{
    public function index(Request $request)
    {
        if($request->expectsJson()){

            $filters = json_decode($request->get('filters'));

            return ResponseJson::successfulResponse(
                Fractal::collection(
                    App::make(AssociatedAccountRepository::class)->list($filters),
                    ListTransformer::class
                )
            );
        }

        abort(404);
    }

    public function selection(Request $request)
    {
        if($request->expectsJson()){

            $filters = json_decode($request->get('filters'));

            return ResponseJson::successfulResponse(
                Fractal::collection(
                    App::make(AssociatedAccountRepository::class)->selection($filters),
                    SelectionTransformer::class,
                    'selection'
                )
            );
        }

        abort(404);
    }
}
