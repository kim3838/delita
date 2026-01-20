<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\RequestApprovalStateRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Transformers\RequestApprovalState\ListTransformer;
use Illuminate\Http\Request;

class UserRequestApprovalStateController extends Controller
{
    public function __construct(
        protected RequestApprovalStateRepository $repository,
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
}
