<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\CompanyCompensationRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Transformers\CompanyCompensation\ListTransformer;
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
}
