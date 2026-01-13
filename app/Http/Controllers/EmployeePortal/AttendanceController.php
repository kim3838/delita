<?php

namespace App\Http\Controllers\EmployeePortal;

use App\Blueprint\Repositories\AttendanceDetailRepository;
use App\Blueprint\Repositories\AttendanceRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeePortal\Attendance\ViewAttendanceRequest;
use App\Transformers\Attendance\ItemTransformer;
use App\Transformers\Attendance\ListTransformer;
use App\Transformers\AttendanceDetail\ListTransformer as AttendanceDetailListTransformer;
use App\Transformers\Overtime\BasicTransformer as OvertimeBasicTransformer;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function __construct(
        protected AttendanceRepository $repository,
        protected AttendanceDetailRepository $detailRepository,
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

    public function showGate(ViewAttendanceRequest $request, $ulid)
    {
        if($request->expectsJson()){

            $attendance = $this->repository->showAndTransformToBasic($ulid);

            return ResponseJson::successfulResponse(['attendance' => $attendance]);
        }

        abort(404);
    }

    public function show(ViewAttendanceRequest $request, $ulid)
    {
        if($request->expectsJson()){

            $filters = json_decode($request->get('filters'));

            $attendance = $this->repository->show($ulid);
            $attendanceDetails = [];

            if($attendance){

                $attendanceDetailFilters = (object)[
                    'attendance_ulid' => $ulid,
                    'shift_breakdown_splits' => $filters?->shift_breakdown_splits
                ];

                $attendanceDetails = $this->detailRepository->list($attendanceDetailFilters);

                $attendanceDetails = Fractal::collection($attendanceDetails, AttendanceDetailListTransformer::class)['data'];
            }

            $overtime = $attendance->overtime ? Fractal::item($attendance->overtime, OvertimeBasicTransformer::class) : $attendance->overtime;
            $attendance = $attendance ? Fractal::item($attendance, ItemTransformer::class) : $attendance;

            return ResponseJson::successfulResponse([
                'attendance' => $attendance,
                'details' => $attendanceDetails,
                'overtime' => $overtime
            ]);
        }

        abort(404);
    }
}
