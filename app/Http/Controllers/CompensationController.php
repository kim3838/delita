<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\CompanyCompensationRepository;
use App\Blueprint\Repositories\CompensationRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\Compensation\BatchDestroyCompensationRequest;
use App\Http\Requests\Compensation\DestroyCompensationRequest;
use App\Http\Requests\Compensation\StoreCompensationRequest;
use App\Http\Requests\Compensation\UpdateCompensationRequest;
use App\Http\Requests\PayrollComponent\ListPayrollComponentRequest;
use App\Transformers\CompanyCompensation\ListTransformer;
use App\Transformers\Compensation\ItemTransformer as CompensationTransformer;
use App\Transformers\Compensation\SelectionTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class CompensationController extends Controller
{
    public function index(ListPayrollComponentRequest $request)
    {
        if(request()->expectsJson()){

            $filters = json_decode($request->get('filters'));

            return ResponseJson::successfulResponse(Fractal::collection(
                App::make(CompanyCompensationRepository::class)->list($filters),
                ListTransformer::class,
                'compensations'
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
                    App::make(CompensationRepository::class)->selection($filters),
                    SelectionTransformer::class,
                    'selection'
                )
            );
        }

        abort(404);
    }

    public function store(StoreCompensationRequest $request)
    {
        if($request->expectsJson()){

            return ResponseJson::successfulResponse([
                'compensation' => Fractal::item(
                    App::make(CompensationRepository::class)->store($request->validated()),
                    CompensationTransformer::class
                )
            ]);
        }

        abort(404);
    }

    public function update(UpdateCompensationRequest $request, $compensationId)
    {
        if($request->expectsJson()){

            return ResponseJson::successfulResponse([
                'compensation' => Fractal::item(
                    App::make(CompensationRepository::class)->update($compensationId, $request->validated()),
                    CompensationTransformer::class
                )
            ]);
        }

        abort(404);
    }

    public function destroy(DestroyCompensationRequest $request, $compensationId)
    {
        if($request->expectsJson()){

            App::make(CompensationRepository::class)->delete($compensationId);

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }

    public function batchDestroy(BatchDestroyCompensationRequest $request)
    {
        if($request->expectsJson()){

            $compensationIds = data_get($request->validated(), 'compensation_ids', []);

            App::make(CompensationRepository::class)->batchDelete($compensationIds);

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }
}
