<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\EmployeeLeaveTypeRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\EmployeeLeaveType\BatchDestroyEmployeeLeaveTypeRequest;
use App\Http\Requests\EmployeeLeaveType\DetachAssignedLeaveTypesRequest;
use App\Http\Requests\EmployeeLeaveType\SyncWithoutDetachingEmployeeLeaveTypeRequest;
use App\Http\Requests\EmployeeLeaveType\UpdateEmployeeLeaveTypeRequest;
use App\Transformers\LeaveTypeAssignment\LeaveTypesByEmployeesTransformer;
use App\Transformers\LeaveTypeAssignment\ListTransformer;
use App\Transformers\LeaveTypeAssignment\PatchableTransformer;
use Illuminate\Http\Request;

class EmployeeLeaveTypeController extends Controller
{
    public function __construct(
        protected EmployeeLeaveTypeRepository $repository
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

    public function update(UpdateEmployeeLeaveTypeRequest $request, $employeeLeaveTypeId)
    {
        if($request->expectsJson()){

            $hydrated = $this->repository->hydrateItem($request->validated());
            $patchableLeaveTypeSettings = Fractal::item($hydrated, PatchableTransformer::class);

            $this->repository->update($employeeLeaveTypeId, $patchableLeaveTypeSettings);

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }

    public function leaveTypesByEmployees(Request $request)
    {
        if(request()->expectsJson()){

            $filters = json_decode($request->get('filters'));

            return ResponseJson::successfulResponse(Fractal::collection(
                $this->repository->leaveTypesByEmployees($filters),
                LeaveTypesByEmployeesTransformer::class
            ));
        }

        abort(404);
    }

    public function syncWithoutDetaching(SyncWithoutDetachingEmployeeLeaveTypeRequest $request)
    {
        if(request()->expectsJson()){

            $employeeIds = data_get($request->validated(), 'employees', []);
            $leaveTypeIds = data_get($request->validated(), 'leave_types', []);

            $hydratedEmployeeLeaveType = $this->repository->hydrateItem($request->validated());
            $patchableEmployeeLeaveTypeSettings = Fractal::item($hydratedEmployeeLeaveType, PatchableTransformer::class);

            $this->repository->syncWithoutDetaching($employeeIds, $leaveTypeIds, $patchableEmployeeLeaveTypeSettings);

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }

    public function detach(DetachAssignedLeaveTypesRequest $request, $morphMapKey)
    {
        if(request()->expectsJson()){

            $selectedMorphables = data_get($request->validated(), 'selectedMorphables', []);

            $this->repository->detachAssignedLeaveTypes($selectedMorphables, $morphMapKey);

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }

    public function batchDestroy(BatchDestroyEmployeeLeaveTypeRequest $request)
    {
        if($request->expectsJson()){

            $leaveTypeAssignmentIds = data_get($request->validated(), 'leave_type_assignment_ids', []);

            $this->repository->batchDelete($leaveTypeAssignmentIds);

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }
}
