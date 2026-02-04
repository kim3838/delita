<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\PayrollRepository;
use App\Enums\PayrollStatus;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\Payroll\StorePayrollRequest;
use App\Transformers\Payroll\BasicTransformer;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function __construct(
        protected readonly PayrollRepository $repository
    ){}

    public function index(Request $request)
    {
        if($request->expectsJson()){

            $filters = json_decode($request->get('filters'));

            return ResponseJson::successfulResponse(
                $this->repository->paginate($filters)
            );
        }

        abort(404);
    }

    public function store(StorePayrollRequest $request)
    {
        if($request->expectsJson()){

            $storePayroll = array_merge($request->validated(), [
                'status' => PayrollStatus::DRAFT
            ]);

            $payroll = $this->repository->store($storePayroll);

            return ResponseJson::successfulResponse([
                'payroll' => Fractal::item($payroll, BasicTransformer::class)
            ]);
        }

        abort(404);
    }
}
