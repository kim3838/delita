<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\LeaveTypeRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Transformers\LeaveType\ListTransformer;
use Illuminate\Http\Request;

class LeaveTypeController extends Controller
{
    public function __construct(
        protected readonly LeaveTypeRepository $repository
    ){}

    public function index(Request $request)
    {
        if($request->expectsJson()){

            $filters = json_decode($request->get('filters'));

            $data = $this->repository->paginate($filters);

            return ResponseJson::successfulResponse(
                Fractal::collection($data, ListTransformer::class)
            );
        }

        abort(404);
    }
}
