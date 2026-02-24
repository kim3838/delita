<?php

namespace App\Http\Controllers;

use App\Blueprint\PayrollServiceInterface;
use App\Blueprint\Repositories\PayrollRepository;
use App\Enums\PayrollStatus;
use App\Exceptions\UnexpectedException;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\Payroll\BatchDestroyPayrollRequest;
use App\Http\Requests\Payroll\ListPayrollRequest;
use App\Http\Requests\Payroll\StorePayrollRequest;
use App\Http\Requests\Payroll\ViewPayrollRequest;
use App\Models\Company;
use App\Transformers\Payroll\BasicTransformer;
use App\Transformers\Payroll\ItemTransformer;
use App\Transformers\Payroll\ListTransformer;
use App\Transformers\Payroll\SelectionTransformer;
use Illuminate\Http\Request;

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

            $payroll = $this->repository->show($payroll->ulid);

            return ResponseJson::successfulResponse([
                'payroll' => Fractal::item($payroll, ItemTransformer::class),
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

    public function batchDestroy(BatchDestroyPayrollRequest $request)
    {
        if($request->expectsJson()){

            $ids = data_get($request->validated(), 'payroll_ids', []);

            $this->repository->batchDelete($ids);

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }
}
