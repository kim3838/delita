<?php

namespace App\Http\Controllers;

use App\Blueprint\PayrollServiceInterface;
use App\Blueprint\Repositories\PayrollRepository;
use App\Enums\PayrollStatus;
use App\Exceptions\UnexpectedException;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\Payroll\StorePayrollRequest;
use App\Models\Company;
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
}
