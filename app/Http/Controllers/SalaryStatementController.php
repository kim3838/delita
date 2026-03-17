<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\SalaryStatementRepository;
use App\Exports\SalaryStatementExport;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\SalaryStatement\BatchDestroySalaryStatementRequest;
use App\Http\Requests\SalaryStatement\BatchUpdateSalaryStatementRequest;
use App\Http\Requests\SalaryStatement\ExportSalaryStatementRequest;
use App\Http\Requests\SalaryStatement\ListSalaryStatementRequest;
use App\Http\Requests\SalaryStatement\ViewSalaryStatementRequest;
use App\Transformers\SalaryStatement\ExportTransformer;
use App\Transformers\SalaryStatement\ItemTransformer;
use App\Transformers\SalaryStatement\ListTransformer;
use App\Transformers\SalaryStatement\TotalsTransformer;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use PhpOffice\PhpSpreadsheet\Exception;

class SalaryStatementController extends Controller
{
    public function __construct(
        protected readonly SalaryStatementRepository $repository
    ){}

    public function index(ListSalaryStatementRequest $request)
    {
        if($request->expectsJson()){

            $filters = json_decode($request->get('filters'));

            list($paginator, $totals) = $this->repository->paginateWithTotals($filters, ['payroll', 'detail_totals']);

            $salaryStatements = Fractal::collection($paginator, ListTransformer::class);
            $salaryStatementTotals = Fractal::item($totals->first(), TotalsTransformer::class);

            return ResponseJson::successfulResponse([
                'salary_statement_totals' => $salaryStatementTotals,
                'salary_statements' => $salaryStatements,
            ]);
        }

        abort(404);
    }

    public function batchUpdate(BatchUpdateSalaryStatementRequest $request)
    {
        if($request->expectsJson()){

            $salaryStatementIdentifiers = data_get($request->validated(), 'salary_statement_identifiers', []);
            $attributes = collect($request->validated())->except(['salary_statement_identifiers'])->toArray();

            $batchUpdateErrors = $this->repository->batchUpdate($salaryStatementIdentifiers, $attributes);

            return ResponseJson::successfulResponse([
                'batch_update_errors' => $batchUpdateErrors
            ]);
        }

        abort(404);
    }

    /**
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function export(ExportSalaryStatementRequest $request)
    {
        if($request->expectsJson()){

            $filters = json_decode($request->get('filters'));

            $data = $this->repository->list($filters, ['payroll', 'detail_totals']);
            $data = Fractal::collection($data, ExportTransformer::class)['data'];

            $export = new SalaryStatementExport(collect($data));

            return ExcelFacade::download($export, 'salary_statements.csv', Excel::CSV);
        }

        abort(404);
    }

    public function show(ViewSalaryStatementRequest $request, $ulid)
    {
        if($request->expectsJson()){

            $salaryStatement = $this->repository->show($ulid);

            $salaryStatement = $salaryStatement ? Fractal::item($salaryStatement, ItemTransformer::class) : $salaryStatement;

            return ResponseJson::successfulResponse([
                'salary_statement' => $salaryStatement,
            ]);
        }

        abort(404);
    }

    public function batchDestroy(BatchDestroySalaryStatementRequest $request)
    {
        if($request->expectsJson()){

            $ids = data_get($request->validated(), 'salary_statement_ids', []);

            $this->repository->batchDelete($ids);

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }
}
