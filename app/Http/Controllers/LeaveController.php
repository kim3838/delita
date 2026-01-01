<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\LeaveRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\Leave\BatchDestroyLeaveRequest;
use App\Http\Requests\Leave\ListLeaveRequest;
use App\Http\Requests\Leave\StoreLeaveRequest;
use App\Transformers\Leave\ListTransformer;

class LeaveController extends Controller
{
    public function __construct(
        protected readonly LeaveRepository $repository
    ){}

    public function indexGate(ListLeaveRequest $request)
    {
        if($request->expectsJson()){

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }

    public function index(ListLeaveRequest $request)
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

    public function store(StoreLeaveRequest $request)
    {
        if($request->expectsJson()){

            return ResponseJson::successfulResponse(
                $this->repository->store($request->validated())
            );
        }

        abort(404);
    }

    public function batchDestroy(BatchDestroyLeaveRequest $request)
    {
        if($request->expectsJson()){

            $attendanceIds = data_get($request->validated(), 'leave_ids', []);

            $this->repository->batchDelete($attendanceIds);

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }
}
