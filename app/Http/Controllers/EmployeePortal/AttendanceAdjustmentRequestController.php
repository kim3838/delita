<?php

namespace App\Http\Controllers\EmployeePortal;

use App\Blueprint\Repositories\AttendanceAdjustmentRequestRepository;
use App\Facades\ResponseJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeePortal\AttendanceAdjustmentRequest\StoreAttendanceAdjustmentRequestRequest as EmployeePortalStoreAttendanceAdjustmentRequestRequest;
use Carbon\Carbon;

class AttendanceAdjustmentRequestController extends Controller
{
    public function __construct(
        protected AttendanceAdjustmentRequestRepository $repository,
    ){}

    public function store(EmployeePortalStoreAttendanceAdjustmentRequestRequest $request)
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
}
