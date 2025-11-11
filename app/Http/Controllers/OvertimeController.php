<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\OvertimeRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\Overtime\UpdateOvertimeRequest;
use App\Transformers\Overtime\ListTransformer;
use Illuminate\Http\Request;

class OvertimeController extends Controller
{
    public function __construct(
        protected readonly OvertimeRepository $repository
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

    public function update(UpdateOvertimeRequest $request, $ulid)
    {
        if($request->expectsJson()){

            $this->repository->update($ulid, $request->validated());

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }
}
