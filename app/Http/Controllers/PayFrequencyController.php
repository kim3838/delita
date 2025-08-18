<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\PayFrequencyRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\PayFrequency\UpdatePayFrequencyRequest;
use App\Transformers\PayFrequency\ItemTransformer;
use App\Transformers\PayFrequency\PatchableTransformer;
use App\Transformers\PayFrequency\SelectionTransformer;
use Illuminate\Http\Request;

class PayFrequencyController extends Controller
{
    public function __construct(
        protected readonly PayFrequencyRepository $repository
    ){}

    public function index(Request $request)
    {
        if($request->expectsJson()){

            $filters = json_decode($request->get('filters'));

            return ResponseJson::successfulResponse(Fractal::collection(
                $this->repository->list($filters),
                PatchableTransformer::class,
                'pay_frequencies'
            ));
        }

        abort(404);
    }

    public function update(UpdatePayFrequencyRequest $request, $payFrequencyId)
    {
        if($request->expectsJson()){

            return ResponseJson::successfulResponse([
                'pay_frequency' => Fractal::item(
                    $this->repository->update($payFrequencyId, $request->validated()),
                    ItemTransformer::class,
                )
            ]);
        }

        abort(404);
    }

    public function selection(Request $request)
    {
        if($request->expectsJson()){

            $filters = json_decode($request->get('filters'));

            return ResponseJson::successfulResponse(Fractal::collection(
                $this->repository->selection($filters),
                SelectionTransformer::class,
                'selection'
            ));
        }

        abort(404);
    }
}
