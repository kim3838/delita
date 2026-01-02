<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\JsonPresetRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\JsonPreset\DestroyJsonPresetRequest;
use App\Http\Requests\JsonPreset\ListJsonPresetRequest;
use App\Http\Requests\JsonPreset\StoreJsonPresetRequest;
use App\Http\Requests\JsonPreset\UpdateJsonPresetRequest;
use App\Http\Requests\JsonPreset\ViewJsonPresetRequest;
use App\Transformers\JsonPreset\BasicTransformer;
use App\Transformers\JsonPreset\ItemTransformer;
use App\Transformers\JsonPreset\ListTransformer;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class JsonPresetController extends Controller
{
    public function __construct(
        protected readonly JsonPresetRepository $repository
    ){}

    public function index(ListJsonPresetRequest $request)
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

    public function showGate(ViewJsonPresetRequest $request, $jsonPresetId)
    {
        if($request->expectsJson()){

            $jsonPreset = $this->repository->show($jsonPresetId);
            $jsonPreset = $jsonPreset ? Fractal::item($jsonPreset, BasicTransformer::class) : $jsonPreset;

            return ResponseJson::successfulResponse(['json_preset' => $jsonPreset]);
        }

        abort(404);
    }

    public function store(StoreJsonPresetRequest $request)
    {
        if($request->expectsJson()){

            return ResponseJson::successfulResponse([
                'json_preset' => Fractal::item(
                    $this->repository->store($request->validated()),
                    ItemTransformer::class
                )
            ]);
        }

        abort(404);
    }

    public function update(UpdateJsonPresetRequest $request, $jsonPresetId)
    {
        if($request->expectsJson()){

            return ResponseJson::successfulResponse([
                'json_preset' => Fractal::item(
                    $this->repository->update($jsonPresetId, $request->validated()),
                    ItemTransformer::class
                )
            ]);
        }

        abort(404);
    }

    public function show(ViewJsonPresetRequest $request, $jsonPresetId)
    {
        if($request->expectsJson()){

            $jsonPreset = $this->repository->show($jsonPresetId);
            $jsonPreset = $jsonPreset ? Fractal::item($jsonPreset, ItemTransformer::class) : $jsonPreset;

            return ResponseJson::successfulResponse(['json_preset' => $jsonPreset]);
        }

        abort(404);
    }

    public function download(Request $request, $jsonPresetId): StreamedResponse
    {
        return $this->repository->download($jsonPresetId);
    }

    public function destroy(DestroyJsonPresetRequest $request, $jsonPresetId)
    {
        if($request->expectsJson()){

            $this->repository->delete($jsonPresetId);

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }
}
