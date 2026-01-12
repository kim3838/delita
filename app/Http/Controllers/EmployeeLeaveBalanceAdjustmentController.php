<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\LeaveBalanceAdjustmentRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Transformers\LeaveBalanceAdjustment\ListTransformer;
use Illuminate\Http\Request;

class EmployeeLeaveBalanceAdjustmentController extends Controller
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
}
