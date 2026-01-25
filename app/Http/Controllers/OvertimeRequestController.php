<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\OvertimeRequestRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\OvertimeRequest\BatchDestroyOvertimeRequestRequest;
use App\Http\Requests\OvertimeRequest\ListOvertimeRequestRequest;
use App\Http\Requests\OvertimeRequest\StoreOvertimeRequestRequest;
use App\Http\Requests\OvertimeRequest\ViewOvertimeRequestRequest;
use App\Transformers\OvertimeRequest\ItemTransformer;
use App\Transformers\OvertimeRequest\ListTransformer;
use Carbon\Carbon;

class OvertimeRequestController extends Controller
{
    public function __construct(
        protected OvertimeRequestRepository $repository,
    ){}

    public function index(ListOvertimeRequestRequest $request)
    {
        if(request()->expectsJson()){

            $filters = json_decode($request->get('filters'));

            return ResponseJson::successfulResponse(Fractal::collection(
                $this->repository->paginate($filters),
                ListTransformer::class
            ));
        }

        abort(404);
    }

    public function show(ViewOvertimeRequestRequest $request, $requestableNumber)
    {
        if(request()->expectsJson()){

            $filters = json_decode($request->get('filters'));

            $overtimeRequest = $this->repository->showFromFilters($filters);

            $overtimeRequest = $overtimeRequest ? Fractal::item($overtimeRequest, ItemTransformer::class) : $overtimeRequest;

            if(empty($overtimeRequest)){
                return ResponseJson::notFoundResponse();
            } else {
                return ResponseJson::successfulResponse($overtimeRequest);
            }
        }

        abort(404);
    }

    public function store(StoreOvertimeRequestRequest $request)
    {
        if(request()->expectsJson()){

            $data = array_merge($request->validated(), [
                'requested_by' => $request->user()->id,
                'date_requested' => Carbon::now()->toDateTimeString()
            ]);

            $this->repository->store($data);

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }

    public function batchDestroy(BatchDestroyOvertimeRequestRequest $request)
    {
        if($request->expectsJson()){

            $overtimeRequestIds = data_get($request->validated(), 'overtime_request_ids', []);

            $this->repository->batchDelete($overtimeRequestIds);

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }
}
