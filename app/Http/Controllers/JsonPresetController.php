<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\JsonPresetRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Transformers\JsonPreset\ItemTransformer;
use App\Transformers\JsonPreset\SelectionTransformer;
use Illuminate\Http\Request;

class JsonPresetController extends Controller
{
    public function __construct(
        protected readonly JsonPresetRepository $repository
    ){}

    public function selection(Request $request)
    {
        if($request->expectsJson()){

            $filters = json_decode($request->get('filters'));

            return ResponseJson::successfulResponse(
                Fractal::collection($this->repository->selection($filters), SelectionTransformer::class)
            );
        }

        abort(404);
    }

    public function show(Request $request, $jsonPresetId)
    {
        if($request->expectsJson()){

            $jsonPreset = $this->repository->show($jsonPresetId);
            $jsonPreset = $jsonPreset ? Fractal::item($jsonPreset, ItemTransformer::class) : $jsonPreset;

            return ResponseJson::successfulResponse(['json_preset' => $jsonPreset]);
        }

        abort(404);
    }
}
