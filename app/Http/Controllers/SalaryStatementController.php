<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\SalaryStatementRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\SalaryStatement\BatchDestroySalaryStatementRequest;
use App\Transformers\SalaryStatement\ListTransformer;
use Illuminate\Http\Request;

class SalaryStatementController extends Controller
{
    public function __construct(
        protected readonly SalaryStatementRepository $repository
    ){}

    public function index(Request $request)
    {
        if($request->expectsJson()){

            $filters = json_decode($request->get('filters'));

            return ResponseJson::successfulResponse(Fractal::collection(
                $this->repository->paginate($filters, ['current_employment_profile']), ListTransformer::class
            ));
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
