<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\AttendanceDetailRepository;
use App\Blueprint\Repositories\AttendanceRepository;
use App\Exceptions\UnexpectedException;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\Attendance\BatchDestroyAttendanceRequest;
use App\Http\Requests\Attendance\UpdateAttendanceRequest;
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
                $this->repository->list($filters),
                ListTransformer::class
            ));
        }

        abort(404);
    }

    public function check(Request $request, $ulid)
    {
        if($request->expectsJson()){

            $attendance = $this->repository->check($ulid);

            return ResponseJson::successfulResponse(['attendance' => $attendance]);
        }

        abort(404);
    }

    public function show(Request $request, $ulid)
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

    /**
     * @throws UnexpectedException
     */
    public function update(UpdateAttendanceRequest $request, $ulid)
    {
        if($request->expectsJson()){

            $this->repository->update($ulid, $request->validated());

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }

    public function batchDestroy(BatchDestroyAttendanceRequest $request)
    {
        if($request->expectsJson()){

            $attendanceIds = data_get($request->validated(), 'attendance_ids', []);

            $this->repository->batchDelete($attendanceIds);

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }
}
