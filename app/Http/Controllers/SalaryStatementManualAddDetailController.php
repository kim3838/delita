<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\FormulaRepository;
use App\Blueprint\Repositories\PayrollRepository;
use App\Blueprint\Repositories\SalaryStatementRepository;
use App\Enums\FormulableComponentSubType;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\SalaryStatementDetailManualAddDetail\SalaryStatementManualAddDetailRequest;
use App\Models\CompanyFormula;
use App\Models\SalaryStatement;
use App\Transformers\Payroll\ItemTransformer as PayrollItemTransformer;

class SalaryStatementManualAddDetailController extends Controller
{
    public function __construct(
        protected readonly SalaryStatementRepository $repository
    ){}

    public function store(SalaryStatementManualAddDetailRequest $request, $salaryStatementUlid)
    {
        if($request->expectsJson()){

            $companyId = data_get($request->validated(), 'company_id');
            $companyFormulaCollection = CompanyFormula::query()->where('company_id', $companyId)->get();
            $companyFormulaIds = $companyFormulaCollection->pluck('formula_id')->toArray();

            $manualAddDetails = data_get($request->validated(), 'manual_add_details', []);
            $refetchPayrollUlid = data_get($request->validated(), 'refetch_payroll_ulid', null);
            $payroll = null;

            $manualSalaryStatementItems = collect($manualAddDetails)
                ->groupBy('component_sub_type')
                ->map(function ($items, $componentSubType) {

                    $amountKey = match($componentSubType){
                        FormulableComponentSubType::MANUAL_EARNING->value => 'taxable',
                        FormulableComponentSubType::MANUAL_DEDUCTION->value => 'deduction',
                        default => 'nontaxable',
                    };

                    return $items->map(function ($item) use ($amountKey){
                        return [
                            'component_name' => $item['component_name'],
                            $amountKey => (string)$item['amount'],
                        ];
                    });
                })
                ->toArray();

            $formulaRepository = app(FormulaRepository::class);

            foreach($manualSalaryStatementItems as $componentSubType => $items){

                $formulableComponentSubType = FormulableComponentSubType::tryFrom($componentSubType);

                $formula = $formulaRepository->formulableComponentSubTypeFormula($formulableComponentSubType);

                if(empty($formula)){
                    return ResponseJson::notFoundResponse('Formula not found');
                }

                if(!in_array($formula->id, $companyFormulaIds)){
                    return ResponseJson::notFoundResponse('Company formula not found');
                }
            }

            $salaryStatement = SalaryStatement::query()->where('ulid', $salaryStatementUlid)->firstOrFail();

            $this->repository->manualAddDetails($salaryStatement, $manualSalaryStatementItems);

            if(!empty($refetchPayrollUlid)){

                $payroll = app(PayrollRepository::class)->show($refetchPayrollUlid);

                $payroll = $payroll ? Fractal::item($payroll, PayrollItemTransformer::class) : $payroll;
            }

            return ResponseJson::successfulResponse([
                'payroll' => $payroll,
            ]);
        }

        abort(404);
    }
}
