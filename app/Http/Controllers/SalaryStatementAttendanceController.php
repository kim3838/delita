<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\SalaryStatementAttendanceRepository;
use App\Exports\PerDaySalaryStatementTotalExport;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\SalaryStatement\ExportSalaryStatementRequest;
use App\Http\Requests\SalaryStatement\ListSalaryStatementRequest;
use App\Transformers\SalaryStatementAttendance\ListTransformer;
use App\Transformers\SalaryStatementAttendance\PerDayStatementTotalsExportTransformer;
use App\Transformers\SalaryStatementAttendance\TotalsTransformer;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use PhpOffice\PhpSpreadsheet\Exception;

class SalaryStatementAttendanceController extends Controller
{
    public function __construct(
        protected readonly SalaryStatementAttendanceRepository $repository
    ){}

    public function index(ListSalaryStatementRequest $request)
    {
        if($request->expectsJson()){

            $filters = json_decode($request->get('filters'));

            if(!empty($filters->assigned_employee_group_ids)){
                $filters->or_employee_ids = $filters->employee_ids;
                unset($filters->employee_ids);
            }

            list($paginator, $totals) = $this->repository->paginateWithTotals($filters, ['salary_statement', 'payroll_components']);

            $perDayStatements = Fractal::collection($paginator, ListTransformer::class);
            $perDayStatementTotals = $totals->first() ? Fractal::item($totals->first(), TotalsTransformer::class) : $totals->first();

            return ResponseJson::successfulResponse([
                'per_day_statement_totals' => $perDayStatementTotals,
                'per_day_statements' => $perDayStatements,
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

            $data = $this->repository->list($filters, ['salary_statement', 'payroll_components']);
            $data = Fractal::collection($data, PerDayStatementTotalsExportTransformer::class)['data'];

            $export = new PerDaySalaryStatementTotalExport(collect($data));

            return ExcelFacade::download($export, 'per_day_statement_totals.csv', Excel::CSV);
        }

        abort(404);
    }
}
