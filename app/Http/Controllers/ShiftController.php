<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\ShiftRepository;
use App\Blueprint\Repositories\ShiftScheduleRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\Shift\DestroyShiftRequest;
use App\Http\Requests\Shift\StoreShiftRequest;
use App\Http\Requests\Shift\UpdateShiftRequest;
use App\Transformers\Shift\ItemTransformer;
use App\Transformers\Shift\ListTransformer;
use App\Transformers\Shift\SelectionTransformer;
use App\Transformers\ShiftSchedule\PatchableTransformer as ShiftSchedulePatchableTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class ShiftController extends Controller
{
    public function __construct(
        protected readonly ShiftRepository $repository
    ){}

    public function index(Request $request)
    {
        if($request->expectsJson()){

            $filters = json_decode($request->get('filters'));

            $data = $this->repository->paginate($filters);

            return ResponseJson::successfulResponse(
                Fractal::collection($data, ListTransformer::class)
            );
        }

        abort(404);
    }

    public function selection(Request $request)
    {
        if(request()->expectsJson()){

            $filters = json_decode($request->get('filters'));

            return ResponseJson::successfulResponse([
                'selection' => Fractal::collection(
                    $this->repository->selection($filters),
                    SelectionTransformer::class
                )
            ]);
        }

        abort(404);
    }

    public function store(StoreShiftRequest $request)
    {
        if($request->expectsJson()){

            $shift = $this->repository->store($request->validated());

            $schedules = collect($request->get('shift_schedules'))->sortBy('week_day');

            $shift->schedules()->createMany($schedules->toArray());

            return ResponseJson::successfulResponse([
                'shift' => Fractal::item($shift, ItemTransformer::class),
            ]);
        }

        abort(404);
    }

    public function update(UpdateShiftRequest $request, $shiftId)
    {
        if($request->expectsJson()){

            $shift = $this->repository->update($shiftId, $request->validated());

            $schedules = collect($request->get('shift_schedules'))->sortBy('week_day');

            foreach($schedules as $schedule){

                App::make(ShiftScheduleRepository::class)->update($schedule['id'], $schedule);
            }

            return ResponseJson::successfulResponse([
                'shift' => Fractal::item($shift, ItemTransformer::class),
            ]);
        }

        abort(404);
    }

    public function show(Request $request, $ulid)
    {
        if($request->expectsJson()){

            $shift = $this->repository->show($ulid);

            $shiftSchedules = [];

            if($shift){

                $shiftScheduleFilters = (object)[
                    'shift_id' => $shift->id
                ];

                $shiftSchedules = App::make(ShiftScheduleRepository::class)->list($shiftScheduleFilters);

                $shiftSchedules = Fractal::collection($shiftSchedules, ShiftSchedulePatchableTransformer::class)['data'];
            }

            $shift = $shift ? Fractal::item($shift, ItemTransformer::class) : $shift;

            return ResponseJson::successfulResponse([
                'shift' => $shift,
                'shift_schedules' => $shiftSchedules,
            ]);
        }

        abort(404);
    }

    public function check(Request $request, $ulid)
    {
        if($request->expectsJson()){

            $shift = $this->repository->showAndTransformToBasic($ulid);

            return ResponseJson::successfulResponse(['shift' => $shift]);
        }

        abort(404);
    }

    public function destroy(DestroyShiftRequest $request, $shiftId)
    {
        if($request->expectsJson()){

            $this->repository->delete($shiftId);

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }
}
