<?php

namespace App\Http\Controllers\EmployeePortal;

use App\Blueprint\Repositories\LeaveRequestRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeePortal\LeaveRequest\BatchDestroyLeaveRequestRequest;
use App\Http\Requests\EmployeePortal\LeaveRequest\StoreLeaveRequestRequest;
use App\Transformers\LeaveRequest\ListTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    public function __construct(
        protected LeaveRequestRepository $repository,
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

    public function store(StoreLeaveRequestRequest $request)
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

    public function batchDestroy(BatchDestroyLeaveRequestRequest $request)
    {
        if($request->expectsJson()){

            $attendanceAdjustmentRequestIds = data_get($request->validated(), 'leave_request_ids', []);

            $this->repository->batchDelete($attendanceAdjustmentRequestIds);

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }
}
