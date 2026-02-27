<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\SalaryStatementRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\SalaryStatement\BatchDestroySalaryStatementRequest;
use App\Http\Requests\SalaryStatement\ListSalaryStatementRequest;
use App\Http\Requests\SalaryStatement\ViewSalaryStatementRequest;
use App\Transformers\SalaryStatement\ItemTransformer;
use App\Transformers\SalaryStatement\ListTransformer;

class SalaryStatementController extends Controller
{
    public function __construct(
        protected readonly SalaryStatementRepository $repository
    ){}

    public function index(ListSalaryStatementRequest $request)
    {
        if($request->expectsJson()){

            $filters = json_decode($request->get('filters'));

            return ResponseJson::successfulResponse(Fractal::collection(
                $this->repository->paginate($filters, ['payroll', 'detail_totals']), ListTransformer::class
            ));
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
