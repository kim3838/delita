<?php

namespace App\Http\Controllers\EmployeePortal;

use App\Blueprint\Repositories\OvertimeRequestRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeePortal\OvertimeRequest\BatchDestroyOvertimeRequestRequest;
use App\Http\Requests\EmployeePortal\OvertimeRequest\StoreOvertimeRequestRequest;
use App\Transformers\OvertimeRequest\ListTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OvertimeRequestController extends Controller
{
    public function __construct(
        protected OvertimeRequestRepository $repository,
    ){}

    public function index(Request $request)
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
