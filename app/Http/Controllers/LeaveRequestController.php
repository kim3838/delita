<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\LeaveRequestRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\LeaveRequest\BatchDestroyLeaveRequestRequest;
use App\Http\Requests\LeaveRequest\ListLeaveRequestRequest;
use App\Http\Requests\LeaveRequest\StoreLeaveRequestRequest;
use App\Http\Requests\LeaveRequest\ViewLeaveRequestRequest;
use App\Transformers\LeaveRequest\ItemTransformer;
use App\Transformers\LeaveRequest\ListTransformer;
use Carbon\Carbon;

class LeaveRequestController extends Controller
{
    public function __construct(
        protected LeaveRequestRepository $repository,
    ){}

    public function index(ListLeaveRequestRequest $request)
    {
        if(request()->expectsJson()){

            $filters = json_decode($request->get('filters'));

            $relations = ['department', 'designation'];

            return ResponseJson::successfulResponse(Fractal::collection(
                $this->repository->paginate($filters, $relations),
                ListTransformer::class
            ));
        }

        abort(404);
    }

    public function show(ViewLeaveRequestRequest $request, $requestableNumber)
    {
        if(request()->expectsJson()){

            $filters = json_decode($request->get('filters'));

            $leaveRequest = $this->repository->showFromFilters($filters);

            $leaveRequest = $leaveRequest ? Fractal::item($leaveRequest, ItemTransformer::class) : $leaveRequest;

            if(empty($leaveRequest)){
                return ResponseJson::notFoundResponse();
            } else {
                return ResponseJson::successfulResponse($leaveRequest);
            }
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
