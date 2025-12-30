<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\FormulaRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\Formula\DestroyFormulaRequest;
use App\Http\Requests\Formula\ListFormulaRequest;
use App\Http\Requests\Formula\StoreFormulaRequest;
use App\Http\Requests\Formula\UpdateFormulaRequest;
use App\Http\Requests\Formula\ViewFormulaRequest;
use App\Transformers\Formula\ItemTransformer;
use App\Transformers\Formula\ListTransformer;
use App\Transformers\Formula\PatchableTransformer;
use App\Transformers\Formula\SelectionTransformer;

class FormulaController extends Controller
{
    public function __construct(
        protected readonly FormulaRepository $repository
    ){}

    public function index(ListFormulaRequest $request)
    {
        if($request->expectsJson()){

            $filters = json_decode($request->get('filters'));

            return ResponseJson::successfulResponse(
                Fractal::collection(
                    $this->repository->paginate($filters),
                    ListTransformer::class
                )
            );
        }

        abort(404);
    }

    public function store(StoreFormulaRequest $request)
    {
        if($request->expectsJson()){

            return ResponseJson::successfulResponse([
                'formula' => Fractal::item(
                    $this->repository->store($request->validated()),
                    ItemTransformer::class
                )
            ]);
        }

        abort(404);
    }

    public function check(ViewFormulaRequest $request, $ulid)
    {
        if($request->expectsJson()){

            return ResponseJson::successfulResponse([
                'formula' => $this->repository->showAndTransformToBasic($ulid)
            ]);
        }

        abort(404);
    }

    public function show(ViewFormulaRequest $request, $ulid)
    {
        if($request->expectsJson()){

            $formula = $this->repository->show($ulid);
            $formula = $formula ? Fractal::item($formula, PatchableTransformer::class) : $formula;

            return ResponseJson::successfulResponse(['formula' => $formula]);
        }

        abort(404);
    }

    public function selection(ListFormulaRequest $request)
    {
        if(request()->expectsJson()){

            $filters = json_decode($request->get('filters'));

            return ResponseJson::successfulResponse([
                'selection' => Fractal::collection(
                    $this->repository->paginate($filters),
                    SelectionTransformer::class
                )
            ]);
        }

        abort(404);
    }

    public function update(UpdateFormulaRequest $request, $formulaId)
    {
        if($request->expectsJson()){

            return ResponseJson::successfulResponse([
                'formula' => Fractal::item(
                    $this->repository->update($formulaId, $request->validated()),
                    ItemTransformer::class
                )
            ]);
        }

        abort(404);
    }

    public function destroy(DestroyFormulaRequest $request, $formulaId)
    {
        if($request->expectsJson()){

            $this->repository->delete($formulaId);

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }
}
