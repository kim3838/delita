<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\LeaveTypeBalancePerPeriodRepository;
use App\Blueprint\Repositories\LeaveTypeRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\LeaveType\BatchDestroyLeaveTypeRequest;
use App\Http\Requests\LeaveType\StoreLeaveTypeRequest;
use App\Http\Requests\LeaveType\UpdateLeaveTypeRequest;
use App\Transformers\LeaveType\ItemTransformer;
use App\Transformers\LeaveType\ListTransformer;
use App\Transformers\LeaveType\SelectionTransformer;
use App\Transformers\LeaveTypeBalancePerPeriod\ListTransformer as LeaveTypeBalancePerPeriodListTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class LeaveTypeController extends Controller
{
    public function __construct(
        protected readonly LeaveTypeRepository $repository,
        protected readonly LeaveTypeBalancePerPeriodRepository $balancePerPeriodRepository
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

    public function selection(Request $request)
    {
        if(request()->expectsJson()){

            $filters = json_decode($request->get('filters'));

            return ResponseJson::successfulResponse([
                'selection' => Fractal::collection(
                    $this->repository->selection($filters),
                    SelectionTransformer::class
                )
            ]);
        }

        abort(404);
    }

    public function store(StoreLeaveTypeRequest $request)
    {
        if($request->expectsJson()){

            $leaveType = $this->repository->store($request->validated());

            $balancePerPeriods = collect($request->validated()['leave_type_balance_per_period'])->map(function ($balancePerPeriod){
                return [
                    'from_period' => $balancePerPeriod['from_period'],
                    'to_period' => $balancePerPeriod['to_period'],
                    'balance' => $balancePerPeriod['balance'],
                ];
            });

            $leaveType->balancePerPeriod()->createMany($balancePerPeriods->toArray());

            return ResponseJson::successfulResponse([
                'leave_type' => Fractal::item($leaveType, ListTransformer::class),
            ]);
        }

        abort(404);
    }

    public function update(UpdateLeaveTypeRequest $request, $leaveTypeUlid)
    {
        if($request->expectsJson()){

            if(!empty($request->validated()['spliced_leave_type_balance_per_period'])){
                $this->balancePerPeriodRepository->batchDelete($request->validated()['spliced_leave_type_balance_per_period']);
            }

            $leaveType = $this->repository->update($leaveTypeUlid, $request->validated());

            $balancePerPeriods = collect($request->validated()['leave_type_balance_per_period'])->filter(function ($balancePerPeriod){
                return isset($balancePerPeriod['id']) && $balancePerPeriod['id'] != null;
            })->map(function ($balancePerPeriod){
                return [
                    'id' => $balancePerPeriod['id'],
                    'leave_type_id' => $balancePerPeriod['leave_type_id'],
                    'from_period' => $balancePerPeriod['from_period'],
                    'to_period' => $balancePerPeriod['to_period'],
                    'balance' => $balancePerPeriod['balance'],
                ];
            });

            $newBalancePerPeriods = collect($request->validated()['leave_type_balance_per_period'])->filter(function ($balancePerPeriod){
                return !isset($balancePerPeriod['id']) || $balancePerPeriod['id'] == null;
            })->map(function ($balancePerPeriod){
                return [
                    'from_period' => $balancePerPeriod['from_period'],
                    'to_period' => $balancePerPeriod['to_period'],
                    'balance' => $balancePerPeriod['balance'],
                ];
            });

            foreach($balancePerPeriods as $balancePerPeriod){
                $this->balancePerPeriodRepository->update($balancePerPeriod['id'], $balancePerPeriod);
            }

            $leaveType->balancePerPeriod()->createMany($newBalancePerPeriods->toArray());

            return ResponseJson::successfulResponse([
                'leave_type' => Fractal::item($leaveType, ListTransformer::class),
            ]);
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

    public function batchDestroy(BatchDestroyLeaveTypeRequest $request)
    {
        if($request->expectsJson()){

            $leaveTypeIds = data_get($request->validated(), 'leave_type_ids', []);

            $this->repository->batchDelete($leaveTypeIds);

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }
}
