<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\AttendanceAdjustmentRequestRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\AttendanceAdjustmentRequest\ListAttendanceAdjustmentRequestRequest;
use App\Transformers\AttendanceAdjustmentRequest\ListTransformer;

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
}
