<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\PayrollRequestRepository;
use App\Enums\PayrollStatus;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\PayrollRequest\BatchDestroyPayrollRequestRequest;
use App\Http\Requests\PayrollRequest\ListPayrollRequestRequest;
use App\Http\Requests\PayrollRequest\StorePayrollRequestRequest;
use App\Http\Requests\PayrollRequest\ViewPayrollRequestRequest;
use App\Transformers\PayrollRequest\ItemTransformer;
use App\Transformers\PayrollRequest\ListTransformer;
use Carbon\Carbon;

class PayrollRequestController extends Controller
{
    public function __construct(
        protected PayrollRequestRepository $repository,
    ){}

    public function index(ListPayrollRequestRequest $request)
    {
        if($request->expectsJson()){

            $filters = json_decode($request->get('filters'));

            return ResponseJson::successfulResponse(Fractal::collection(
                $this->repository->paginate($filters),
                ListTransformer::class
            ));
        }

        abort(404);
    }

    public function show(ViewPayrollRequestRequest $request, $requestableNumber)
    {
        if($request->expectsJson()){

            $filters = json_decode($request->get('filters'));

            $payrollRequest = $this->repository->showFromFilters($filters);

            $payrollRequest = $payrollRequest ? Fractal::item($payrollRequest, ItemTransformer::class) : $payrollRequest;

            if(empty($payrollRequest)){
                return ResponseJson::notFoundResponse();
            } else {
                return ResponseJson::successfulResponse($payrollRequest);
            }
        }

        abort(404);
    }

    public function store(StorePayrollRequestRequest $request)
    {
        if($request->expectsJson()){

            $data = array_merge($request->validated(), [
                'requested_by' => $request->user()->id,
                'date_requested' => Carbon::now()->toDateTimeString()
            ]);

            $payrollRequest = $this->repository->store($data);

            $payrollRequest->payroll->update([
                'status' => PayrollStatus::WORKFLOW_IN_PROGRESS->value,
            ]);

            return ResponseJson::successfulResponse([
                'payroll_request' => [
                    'number' => $payrollRequest->number,
                ],
            ]);
        }

        abort(404);
    }

    public function batchDestroy(BatchDestroyPayrollRequestRequest $request)
    {
        if($request->expectsJson()){

            $payrollRequestIds = data_get($request->validated(), 'payroll_request_ids', []);

            $this->repository->batchDelete($payrollRequestIds);

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }
}
