<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\LeaveTypeBalancePerPeriodRepository;
use App\Blueprint\Repositories\LeaveTypeRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Transformers\LeaveType\ItemTransformer;
use App\Transformers\LeaveType\ListTransformer;
use App\Transformers\LeaveTypeBalancePerPeriod\ListTransformer as LeaveTypeBalancePerPeriodListTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

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

    public function show(Request $request, $ulid)
    {
        if($request->expectsJson()){

            $leaveType = $this->repository->show($ulid);

            $balancePerPeriod = [];

            if($leaveType){

                $leaveTypeBalancePerPeriodFilters = (object)[
                    'leave_type_id' => $leaveType->id
                ];

                $balancePerPeriod = App::make(LeaveTypeBalancePerPeriodRepository::class)->list($leaveTypeBalancePerPeriodFilters);

                $balancePerPeriod = Fractal::collection($balancePerPeriod, LeaveTypeBalancePerPeriodListTransformer::class)['data'];
            }

            $leaveType = $leaveType ? Fractal::item($leaveType, ItemTransformer::class) : $leaveType;

            return ResponseJson::successfulResponse([
                'leave_type' => $leaveType,
                'leave_type_balance_per_period' => $balancePerPeriod,
            ]);
        }

        abort(404);
    }

    public function check(Request $request, $ulid)
    {
        if($request->expectsJson()){

            $leaveType = $this->repository->check($ulid);

            return ResponseJson::successfulResponse(['leave_type' => $leaveType]);
        }

        abort(404);
    }
}
