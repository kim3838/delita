<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\CompanyUserRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Transformers\CompanyUser\ListTransformer;
use App\Transformers\CompanyUser\SelectionTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class CompanyUserController extends Controller
{
    public function index(Request $request)
    {
        if(request()->expectsJson()){

            $filters = json_decode($request->get('filters'));

            return ResponseJson::successfulResponse(Fractal::collection(
                App::make(CompanyUserRepository::class)->paginate($filters),
                ListTransformer::class
            ));
        }

        abort(404);
    }

    public function selection(Request $request)
    {
        if(request()->expectsJson()){

            $filters = json_decode($request->get('filters'));

            return ResponseJson::successfulResponse([
                'selection' => Fractal::collection(
                    App::make(CompanyUserRepository::class)->selection($filters),
                    SelectionTransformer::class
                )
            ]);
        }

        abort(404);
    }
}
