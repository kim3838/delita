<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\LeaveBalanceAdjustmentRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\LeaveBalanceAdjustment\BatchDestroyLeaveBalanceAdjustmentRequest;
use App\Http\Requests\LeaveBalanceAdjustment\StoreLeaveBalanceAdjustmentRequest;
use App\Http\Requests\LeaveBalanceAdjustment\UpdateLeaveBalanceAdjustmentRequest;
use App\Transformers\LeaveBalanceAdjustment\ListTransformer;
use Illuminate\Http\Request;

class LeaveBalanceAdjustmentController extends Controller
{
    public function __construct(
        protected readonly LeaveBalanceAdjustmentRepository $repository
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

    public function store(StoreLeaveBalanceAdjustmentRequest $request)
    {
        if($request->expectsJson()){

            $this->repository->store($request->validated());

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }

    public function update(UpdateLeaveBalanceAdjustmentRequest $request, $ulid)
    {
        if($request->expectsJson()){

            $this->repository->update($ulid, $request->validated());

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }

    public function batchDestroy(BatchDestroyLeaveBalanceAdjustmentRequest $request)
    {
        if($request->expectsJson()){

            $ids = data_get($request->validated(), 'leave_balance_adjustment_ids', []);

            $this->repository->batchDelete($ids);

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }
}
