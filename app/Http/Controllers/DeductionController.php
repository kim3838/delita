<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\CompanyDeductionRepository;
use App\Blueprint\Repositories\DeductionRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\Deduction\DestroyDeductionRequest;
use App\Http\Requests\Deduction\StoreDeductionRequest;
use App\Http\Requests\Deduction\UpdateDeductionRequest;
use App\Transformers\CompanyDeduction\ListTransformer;
use App\Transformers\Deduction\ItemTransformer as DeductionTransformer;
use App\Transformers\Deduction\SelectionTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class DeductionController extends Controller
{
    public function index(Request $request)
    {
        if(request()->expectsJson()){

            $filters = json_decode($request->get('filters'));

            return ResponseJson::successfulResponse(Fractal::collection(
                App::make(CompanyDeductionRepository::class)->list($filters),
                ListTransformer::class,
                'deductions'
            ));
        }

        abort(404);
    }

    public function selection(Request $request)
    {
        if(request()->expectsJson()){

            $filters = json_decode($request->get('filters'));

            return ResponseJson::successfulResponse(
                Fractal::collection(
                    App::make(DeductionRepository::class)->selection($filters),
                    SelectionTransformer::class,
                    'selection'
                )
            );
        }

        abort(404);
    }

    public function store(StoreDeductionRequest $request)
    {
        if($request->expectsJson()){

            return ResponseJson::successfulResponse([
                'deduction' => Fractal::item(
                    App::make(DeductionRepository::class)->store($request->validated()),
                    DeductionTransformer::class
                )
            ]);
        }

        abort(404);
    }

    public function update(UpdateDeductionRequest $request, $deductionId)
    {
        if($request->expectsJson()){

            return ResponseJson::successfulResponse([
                'deduction' => Fractal::item(
                    App::make(DeductionRepository::class)->update($deductionId, $request->validated()),
                    DeductionTransformer::class
                )
            ]);
        }

        abort(404);
    }

    public function destroy(DestroyDeductionRequest $request, $deductionId)
    {
        if($request->expectsJson()){

            App::make(DeductionRepository::class)->delete($deductionId);

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }
}
