<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\PayrollRepository;
use App\Enums\PayrollStatus;
use App\Exceptions\UnexpectedException;
use App\Exports\PayrollExport;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\Payroll\BatchDestroyPayrollRequest;
use App\Http\Requests\Payroll\ExportPayrollRequest;
use App\Http\Requests\Payroll\ListPayrollRequest;
use App\Http\Requests\Payroll\StorePayrollRequest;
use App\Http\Requests\Payroll\ViewPayrollRequest;
use App\Jobs\GeneratePayroll;
use App\Models\Company;
use App\Transformers\Payroll\ExportTransformer;
use App\Transformers\Payroll\ItemTransformer;
use App\Transformers\Payroll\ListTransformer;
use App\Transformers\Payroll\SelectionTransformer;
use App\Transformers\Payroll\TotalsTransformer;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use PhpOffice\PhpSpreadsheet\Exception;

class PayrollController extends Controller
{
    public function __construct(
        protected readonly PayrollRepository $repository
    ){}

    public function index(ListPayrollRequest $request)
    {
        if($request->expectsJson()){

            $filters = json_decode($request->get('filters'));

            list($paginator, $totals) = $this->repository->paginateWithTotals($filters, ['salary_statement']);

            $payrolls = Fractal::collection($paginator, ListTransformer::class);
            $payrollTotals = $totals->first() ? Fractal::item($totals->first(), TotalsTransformer::class) : $totals->first();

            return ResponseJson::successfulResponse([
                'payroll_totals' => $payrollTotals,
                'payrolls' => $payrolls,
            ]);
        }

        abort(404);
    }

    /**
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function export(ExportPayrollRequest $request)
    {
        if($request->expectsJson()){

            $filters = json_decode($request->get('filters'));

            $data = $this->repository->list($filters, ['salary_statement']);
            $data = Fractal::collection($data, ExportTransformer::class)['data'];

            $export = new PayrollExport(collect($data));

            return ExcelFacade::download($export, 'payrolls.csv', Excel::CSV);
        }

        abort(404);
    }

    public function selection(Request $request)
    {
        if(request()->expectsJson()){

            $filters = json_decode($request->get('filters'));

            return ResponseJson::successfulResponse([
                'selection' => Fractal::collection(
                    $this->repository->selection($filters, ['salary_statement']),
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
            $employeeIds = $request->validated()['employee_ids'];

            $storePayroll = array_merge($request->validated(), [
                'status' => PayrollStatus::GENERATING
            ]);

            $payroll = $this->repository->store($storePayroll);

            GeneratePayroll::dispatch(
                Company::find($companyId),
                $request->user(),
                $payroll,
                $employeeIds
            );

            return ResponseJson::successfulResponse();
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
