<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\CompanyCompensationRepository;
use App\Blueprint\Repositories\CompensationRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\StoreCompensationRequest;
use App\Transformers\CompanyCompensation\ListTransformer;
use App\Transformers\Compensation\ItemTransformer as CompensationTransformer;
use Illuminate\Support\Facades\App;

class CompanyCompensationController extends Controller
{
    public function index()
    {
        if(request()->expectsJson()){
            return ResponseJson::successfulResponse(Fractal::collection(
                App::make(CompanyCompensationRepository::class)->list(),
                ListTransformer::class,
                'compensations'
            ));
        }

        abort(404);
    }

    public function store(StoreCompensationRequest $request)
    {
        if(request()->expectsJson()){
            return ResponseJson::successfulResponse([
                'company_compensation' => Fractal::item(
                    App::make(CompensationRepository::class)->store($request->validated()),
                    CompensationTransformer::class
                )
            ]);
        }

        abort(404);
    }
}
