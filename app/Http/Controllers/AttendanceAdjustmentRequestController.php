<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\AttendanceAdjustmentRequestRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\AttendanceAdjustmentRequest\BatchDestroyAttendanceAdjustmentRequestRequest;
use App\Http\Requests\AttendanceAdjustmentRequest\ListAttendanceAdjustmentRequestRequest;
use App\Http\Requests\AttendanceAdjustmentRequest\StoreAttendanceAdjustmentRequestRequest;
use App\Http\Requests\AttendanceAdjustmentRequest\ViewAttendanceAdjustmentRequestRequest;
use App\Transformers\AttendanceAdjustmentRequest\ItemTransformer;
use App\Transformers\AttendanceAdjustmentRequest\ListTransformer;
use Carbon\Carbon;

class AttendanceAdjustmentRequestController extends Controller
{
    public function __construct(
        protected AttendanceAdjustmentRequestRepository $repository,
    ){}

    public function index(ListAttendanceAdjustmentRequestRequest $request)
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

    public function show(ViewAttendanceAdjustmentRequestRequest $request, $requestableNumber)
    {
        if(request()->expectsJson()){

            $filters = json_decode($request->get('filters'));

            $attendanceAdjustment = $this->repository->showFromFilters($filters);

            $attendanceAdjustment = $attendanceAdjustment ? Fractal::item($attendanceAdjustment, ItemTransformer::class) : $attendanceAdjustment;

            return ResponseJson::successfulResponse([
                'attendance_adjustment' => $attendanceAdjustment
            ]);
        }

        abort(404);
    }

    public function store(StoreAttendanceAdjustmentRequestRequest $request)
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

    public function batchDestroy(BatchDestroyAttendanceAdjustmentRequestRequest $request)
    {
        if($request->expectsJson()){

            $attendanceAdjustmentRequestIds = data_get($request->validated(), 'attendance_adjustment_request_ids', []);

            $this->repository->batchDelete($attendanceAdjustmentRequestIds);

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }
}
