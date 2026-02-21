<?php

namespace App\Http\Controllers;

use App\Blueprint\PayrollServiceInterface;
use App\Blueprint\Repositories\PayrollRepository;
use App\Enums\PayrollStatus;
use App\Exceptions\UnexpectedException;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\Payroll\ListPayrollRequest;
use App\Http\Requests\Payroll\StorePayrollRequest;
use App\Http\Requests\Payroll\ViewPayrollRequest;
use App\Models\Company;
use App\Transformers\Payroll\BasicTransformer;
use App\Transformers\Payroll\ItemTransformer;
use App\Transformers\Payroll\ListTransformer;

class PayrollController extends Controller
{
    public function __construct(
        protected readonly PayrollRepository $repository
    ){}

    public function index(ListPayrollRequest $request)
    {
        if($request->expectsJson()){

            $filters = json_decode($request->get('filters'));

            return ResponseJson::successfulResponse(Fractal::collection(
                $this->repository->paginate($filters, ['salary_statement']),
                ListTransformer::class
            ));
        }

        abort(404);
    }

    /**
     * @throws UnexpectedException
     */
    public function store(StorePayrollRequest $request)
    {
        if($request->expectsJson()){

            $companyId = $request->validated()['company_id'];

            $storePayroll = array_merge($request->validated(), [
                'status' => PayrollStatus::DRAFT
            ]);

            $payroll = $this->repository->store($storePayroll);

            $payrollService = app(PayrollServiceInterface::class, [Company::find($companyId)]);

            $payrollService->generateSalaryStatements($payroll);

            return ResponseJson::successfulResponse([
                'payroll' => Fractal::item($payroll, BasicTransformer::class),
                'salary_statements' => []
            ]);
        }

        abort(404);
    }

    public function show(ViewPayrollRequest $request, $ulid)
    {
        if($request->expectsJson()){

            $payroll = $this->repository->show($ulid);

            $payroll = $payroll ? Fractal::item($payroll, ItemTransformer::class) : $payroll;

            return ResponseJson::successfulResponse([
                'payroll' => $payroll,
            ]);
        }

        abort(404);
    }
}
