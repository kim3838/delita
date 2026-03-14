<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\SalaryStatementRepository;
use App\Enums\FormulableComponentSubType;
use App\Facades\ResponseJson;
use App\Http\Requests\SalaryStatementDetailManualAddDetail\SalaryStatementManualAddDetailRequest;
use App\Models\SalaryStatement;

class SalaryStatementManualAddDetailController extends Controller
{
    public function __construct(
        protected readonly SalaryStatementRepository $repository
    ){}

    public function store(SalaryStatementManualAddDetailRequest $request, $salaryStatementUlid)
    {
        if($request->expectsJson()){

            $manualAddDetails = data_get($request->validated(), 'manual_add_details', []);

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

            $salaryStatement = SalaryStatement::query()->where('ulid', $salaryStatementUlid)->firstOrFail();

            $this->repository->manualAddDetails($salaryStatement, $manualSalaryStatementItems);

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }
}
