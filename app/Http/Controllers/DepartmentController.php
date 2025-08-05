<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\DepartmentRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\Department\DestroyDepartmentRequest;
use App\Http\Requests\Department\StoreDepartmentRequest;
use App\Http\Requests\Department\UpdateDepartmentRequest;
use App\Transformers\Department\ItemTransformer;
use App\Transformers\Department\ListTransformer;
use App\Transformers\Department\PatchableTransformer;
use App\Transformers\Department\SelectionTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        if($request->expectsJson()){

            $filters = json_decode($request->get('filters'));

            return ResponseJson::successfulResponse(Fractal::collection(
                App::make(DepartmentRepository::class)->list($filters),
                ListTransformer::class,
                'departments'
            ));
        }

        abort(404);
    }

    public function selection(Request $request)
    {
        if(request()->expectsJson()){

            $filters = json_decode($request->get('filters'));

            return ResponseJson::successfulResponse(
                Fractal::collection(
                    App::make(DepartmentRepository::class)->selection($filters),
                    SelectionTransformer::class,
                    'selection'
                )
            );
        }

        abort(404);
    }

    public function store(StoreDepartmentRequest $request)
    {
        if($request->expectsJson()){

            return ResponseJson::successfulResponse([
                'department' => Fractal::item(
                    App::make(DepartmentRepository::class)->store($request->validated()),
                    ItemTransformer::class
                )
            ]);
        }

        abort(404);
    }

    public function update(UpdateDepartmentRequest $request, $departmentId)
    {
        if($request->expectsJson()){

            $hydrated = App::make(DepartmentRepository::class)->hydrateItem($request->validated());
            $patchable = Fractal::item($hydrated, PatchableTransformer::class);

            return ResponseJson::successfulResponse([
                'department' => Fractal::item(
                    App::make(DepartmentRepository::class)->update($departmentId, $patchable),
                    ItemTransformer::class
                )
            ]);
        }

        abort(404);
    }

    public function destroy(DestroyDepartmentRequest $request, $departmentId)
    {
        if($request->expectsJson()){

            App::make(DepartmentRepository::class)->delete($departmentId);

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }
}
