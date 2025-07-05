<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\DesignationRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\Designation\DestroyDesignationRequest;
use App\Http\Requests\Designation\StoreDesignationRequest;
use App\Http\Requests\Designation\UpdateDesignationRequest;
use App\Transformers\Designation\ItemTransformer;
use App\Transformers\Designation\ListTransformer;
use App\Transformers\Designation\SelectionTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class DesignationController extends Controller
{
    public function index(Request $request)
    {
        if($request->expectsJson()){

            return ResponseJson::successfulResponse(Fractal::collection(
                App::make(DesignationRepository::class)->list(),
                ListTransformer::class,
                'designations'
            ));
        }

        abort(404);
    }

    public function selection()
    {
        if(request()->expectsJson()){

            return ResponseJson::successfulResponse(
                Fractal::collection(
                    App::make(DesignationRepository::class)->selection(),
                    SelectionTransformer::class,
                    'selection'
                )
            );
        }

        abort(404);
    }

    public function store(StoreDesignationRequest $request)
    {
        if($request->expectsJson()){

            return ResponseJson::successfulResponse([
                'designation' => Fractal::item(
                    App::make(DesignationRepository::class)->store($request->validated()),
                    ItemTransformer::class
                )
            ]);
        }

        abort(404);
    }

    public function update(UpdateDesignationRequest $request, $designationId)
    {
        if($request->expectsJson()){

            return ResponseJson::successfulResponse([
                'designation' => Fractal::item(
                    App::make(DesignationRepository::class)->update($designationId, $request->validated()),
                    ItemTransformer::class
                )
            ]);
        }

        abort(404);
    }

    public function destroy(DestroyDesignationRequest $request, $designationId)
    {
        if($request->expectsJson()){

            App::make(DesignationRepository::class)->delete($designationId);

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }
}
