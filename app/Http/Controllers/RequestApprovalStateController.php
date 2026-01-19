<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\RequestApprovalStateRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\RequestApprovalState\ListRequestApprovalStateRequest;
use App\Transformers\RequestApprovalState\ListTransformer;

class RequestApprovalStateController extends Controller
{
    public function __construct(
        protected RequestApprovalStateRepository $repository,
    ){}

    public function index(ListRequestApprovalStateRequest $request)
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
}
