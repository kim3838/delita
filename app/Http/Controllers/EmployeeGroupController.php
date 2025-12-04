<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\EmployeeGroupRepository;
use App\Enums\GroupType;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\EmployeeGroup\BatchDestroyEmployeeGroupRequest;
use App\Http\Requests\EmployeeGroup\DetachAssignedGroupsRequest;
use App\Http\Requests\EmployeeGroup\StoreEmployeeGroupRequest;
use App\Http\Requests\EmployeeGroup\SyncWithoutDetachingEmployeeGroupRequest;
use App\Http\Requests\EmployeeGroup\UpdateEmployeeGroupRequest;
use App\Transformers\EmployeeGroup\ListTransformer;
use App\Transformers\EmployeeGroup\ItemTransformer;
use App\Transformers\EmployeeGroup\SelectionTransformer;
use Illuminate\Http\Request;

class EmployeeGroupController extends Controller
{
    public function __construct(
        protected readonly EmployeeGroupRepository $repository
    ){}

    public function index(Request $request)
    {
        if($request->expectsJson()){

            $filters = json_decode($request->get('filters'));
            $filters->type = [GroupType::EMPLOYEE];

            return ResponseJson::successfulResponse(
                Fractal::collection(
                    $this->repository->paginate($filters),
                    ListTransformer::class
                )
            );
        }

        abort(404);
    }

    public function selection(Request $request)
    {
        if(request()->expectsJson()){

            $filters = json_decode($request->get('filters'));

            return ResponseJson::successfulResponse(
                Fractal::collection(
                    $this->repository->selection($filters),
                    SelectionTransformer::class,
                    'selection'
                )
            );
        }

        abort(404);
    }

    public function store(StoreEmployeeGroupRequest $request)
    {
        if($request->expectsJson()){

            $data = array_merge($request->validated(), ['type' => GroupType::EMPLOYEE]);

            $group = $this->repository->store($data);

            $group->employees()->sync($data['employees']);

            return ResponseJson::successfulResponse([
                'group' => Fractal::item($group, ItemTransformer::class)
            ]);
        }

        abort(404);
    }

    public function update(UpdateEmployeeGroupRequest $request, $ulid)
    {
        if($request->expectsJson()){

            $data = array_merge($request->validated(), ['type' => GroupType::EMPLOYEE]);

            $group = $this->repository->update($ulid, $data);

            $group->employees()->sync($data['employees']);

            return ResponseJson::successfulResponse([
                'group' => Fractal::item($group, ItemTransformer::class)
            ]);
        }

        abort(404);
    }

    public function syncWithoutDetaching(SyncWithoutDetachingEmployeeGroupRequest $request)
    {
        if(request()->expectsJson()){

            $employeeIds = data_get($request->validated(), 'employees', []);
            $groupIds = data_get($request->validated(), 'groups', []);

            $this->repository->syncWithoutDetaching($employeeIds, $groupIds);

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }

    public function detach(DetachAssignedGroupsRequest $request)
    {
        if(request()->expectsJson()){

            $groupIds = data_get($request->validated(), 'groups', []);

            $this->repository->detachAssignedGroups($groupIds);

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }

    public function batchDestroy(BatchDestroyEmployeeGroupRequest $request)
    {
        if($request->expectsJson()){

            $groupIds = data_get($request->validated(), 'group_ids', []);

            $this->repository->batchDelete($groupIds);

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }
}
