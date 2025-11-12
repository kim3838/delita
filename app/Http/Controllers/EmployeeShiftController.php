<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\EmployeeShiftRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\EmployeeShift\BatchDestroyEmployeeShiftRequest;
use App\Http\Requests\EmployeeShift\SyncWithoutDetachingEmployeeShiftRequest;
use App\Http\Requests\EmployeeShift\DestroyEmployeeShiftRequest;
use App\Http\Requests\EmployeeShift\DetachAssignedShiftsRequest;
use App\Http\Requests\EmployeeShift\UpdateEmployeeShiftRequest;
use App\Transformers\ShiftAssignment\ListTransformer;
use App\Transformers\ShiftAssignment\PatchableTransformer;
use App\Transformers\ShiftAssignment\SelectionTransformer;
use App\Transformers\ShiftAssignment\ShiftsByEmployeesTransformer;
use Illuminate\Http\Request;

class EmployeeShiftController extends Controller
{
    public function __construct(
        protected EmployeeShiftRepository $repository
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

    public function update(UpdateEmployeeShiftRequest $request, $employeeShiftId)
    {
        if($request->expectsJson()){

            $hydrated = $this->repository->hydrateItem($request->validated());
            $patchableShiftSettings = Fractal::item($hydrated, PatchableTransformer::class);

            $this->repository->update($employeeShiftId, $patchableShiftSettings);

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }

    public function shiftsByEmployees(Request $request)
    {
        if(request()->expectsJson()){

            $filters = json_decode($request->get('filters'));

            return ResponseJson::successfulResponse(Fractal::collection(
                $this->repository->shiftsByEmployees($filters),
                ShiftsByEmployeesTransformer::class
            ));
        }

        abort(404);
    }

    public function syncWithoutDetaching(SyncWithoutDetachingEmployeeShiftRequest $request)
    {
        if(request()->expectsJson()){

            $employeeIds = data_get($request->validated(), 'employees', []);
            $shiftIds = data_get($request->validated(), 'shifts', []);

            $hydratedEmployeeShift = $this->repository->hydrateItem($request->validated());
            $patchableEmployeeShiftSettings = Fractal::item($hydratedEmployeeShift, PatchableTransformer::class);

            $this->repository->syncWithoutDetaching($employeeIds, $shiftIds, $patchableEmployeeShiftSettings);

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }

    public function detach(DetachAssignedShiftsRequest $request, $morphMapKey)
    {
        if(request()->expectsJson()){

            $selectedMorphables = data_get($request->validated(), 'selectedMorphables', []);

            $this->repository->detachAssignedShifts($selectedMorphables, $morphMapKey);

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }

    public function destroy(DestroyEmployeeShiftRequest $request, $employeeShiftId)
    {
        if($request->expectsJson()){

            $this->repository->delete($employeeShiftId);

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }

    public function batchDestroy(BatchDestroyEmployeeShiftRequest $request)
    {
        if($request->expectsJson()){

            $shiftAssignmentIds = data_get($request->validated(), 'shift_assignment_ids', []);

            $this->repository->batchDelete($shiftAssignmentIds);

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }
}
