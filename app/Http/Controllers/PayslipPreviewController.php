<?php

namespace App\Http\Controllers;

use App\Blueprint\PayslipServiceInterface;
use App\Blueprint\Repositories\SalaryStatementRepository;
use App\Facades\ResponseJson;
use App\Http\Requests\SalaryStatement\ViewSalaryStatementRequest;

class PayslipPreviewController extends Controller
{
    public function __construct(
        protected readonly SalaryStatementRepository $salaryStatementRepository
    ){}

    public function __invoke(ViewSalaryStatementRequest $request, $ulid)
    {
        if($request->expectsJson()){

            $salaryStatement = $this->salaryStatementRepository->show($ulid);

            $payslipService = app(PayslipServiceInterface::class, [$salaryStatement->payroll->company]);

            $signed = $payslipService->getSigned($salaryStatement);

            return ResponseJson::successfulResponse([
                'signed' => $signed
            ]);
        }

        abort(404);
    }
}
