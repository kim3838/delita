<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\RoleRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\Role\BatchDestroyRoleRequest;
use App\Http\Requests\Role\ListRoleRequest;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Http\Requests\Role\ViewRoleRequest;
use App\Transformers\Role\AccountRoleSelectionTransformer;
use App\Transformers\Role\ItemTransformer;
use App\Transformers\Role\ListTransformer;
use App\Transformers\Role\SelectionTransformer;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function __construct(
        protected readonly RoleRepository $repository
    ){}

    public function index(ListRoleRequest $request)
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

    public function store(StoreRoleRequest $request)
    {
        if($request->expectsJson()){

            $permissionIds = data_get($request->validated(), 'permission_ids', []);

            $role = $this->repository->store(array_merge(
                $request->validated(),
                ['guard_name' => 'web']
            ));

            $role->syncPermissions($permissionIds);

            $role = $role ? Fractal::item($role, ItemTransformer::class) : $role;

            return ResponseJson::successfulResponse([
                'role' => $role,
            ]);
        }

        abort(404);
    }

    public function selection(Request $request)
    {
        if(request()->expectsJson()){

            $filters = json_decode($request->get('filters'));

            $transformer = $request->user()->isSuperAdmin()
                ? AccountRoleSelectionTransformer::class
                : SelectionTransformer::class;

            return ResponseJson::successfulResponse(
                Fractal::collection($this->repository->selection($filters), $transformer, 'selection')
            );
        }

        abort(404);
    }

    public function show(ViewRoleRequest $request, $ulid)
    {
        if($request->expectsJson()){

            $role = $this->repository->show($ulid);

            $role = $role ? Fractal::item($role, ItemTransformer::class) : $role;

            return ResponseJson::successfulResponse([
                'role' => $role,
                'role_permissions' => $this->repository->permissionMap($ulid),
            ]);
        }

        abort(404);
    }

    public function permissionTemplate(Request $request)
    {
        if($request->expectsJson()){

            return ResponseJson::successfulResponse([
                'role_permissions' => $this->repository->permissionMap(),
            ]);
        }

        abort(404);
    }

    public function update(UpdateRoleRequest $request, $ulid)
    {
        if($request->expectsJson()){

            $permissionIds = data_get($request->validated(), 'permission_ids', []);

            $role = $this->repository->update($ulid, $request->validated());

            $role->syncPermissions($permissionIds);

            $role = $role ? Fractal::item($role, ItemTransformer::class) : $role;

            return ResponseJson::successfulResponse([
                'role' => $role
            ]);
        }

        abort(404);
    }

    public function batchDestroy(BatchDestroyRoleRequest $request)
    {
        if($request->expectsJson()){

            $roleIds = data_get($request->validated(), 'role_ids', []);

            $this->repository->batchDelete($roleIds);

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }
}
