<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\SalaryStatementRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\EmployeePortal\SalaryStatement\ViewSalaryStatementRequest;
use App\Transformers\SalaryStatement\ItemTransformer;
use App\Transformers\SalaryStatement\ListTransformer;
use Illuminate\Http\Request;

class EmployeeSalaryStatementController extends Controller
{
    public function __construct(
        protected readonly SalaryStatementRepository $repository
    ){}

    public function index(Request $request)
    {
        if($request->expectsJson()){

            $filters = json_decode($request->get('filters'));

            return ResponseJson::successfulResponse(Fractal::collection(
                $this->repository->paginate($filters, ['payroll', 'current_employment_profile']), ListTransformer::class
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
}
