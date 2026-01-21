<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\UserFiledRequestRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Transformers\UserFiledRequest\ListTransformer;
use Illuminate\Http\Request;

class UserFiledRequestController extends Controller
{
    public function __construct(
        protected UserFiledRequestRepository $repository,
    ){}

    public function index(Request $request)
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
