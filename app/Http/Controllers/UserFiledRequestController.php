<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\UserFiledRequestRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\EmployeePortal\UserFiledRequest\BatchDestroyUserFiledRequestRequest;
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

    public function batchDestroy(BatchDestroyUserFiledRequestRequest $request)
    {
        if($request->expectsJson()){

            $requestables = data_get($request->validated(), 'requestables', []);

            foreach($requestables as $requestable => $ids){
                app($requestable)->batchDelete($ids);
            }

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }
}
