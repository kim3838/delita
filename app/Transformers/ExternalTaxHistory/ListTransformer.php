<?php

namespace App\Transformers\ExternalTaxHistory;

use App\Models\ExternalTaxHistory;
use Brick\Math\BigDecimal;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(ExternalTaxHistory $externalTaxHistory): array
    {
        $totalTaxable = BigDecimal::of($externalTaxHistory->total_taxable);
        $totalNontaxableBonus = BigDecimal::of($externalTaxHistory->total_nontaxable_bonus);
        $totalTaxableFromBonus = BigDecimal::of($externalTaxHistory->total_taxable_from_bonus);
        $totalTaxWithheld = BigDecimal::of($externalTaxHistory->total_tax_withheld);

        return [
            'row_number' => $externalTaxHistory->row_number,
            'id' => $externalTaxHistory->id,
            'ulid' => $externalTaxHistory->ulid,
            'employee_id' => $externalTaxHistory->employee_id,

            'year' => $externalTaxHistory->year,
            'total_taxable' => $totalTaxable->toScale(2)->toString(),
            'total_nontaxable_bonus' => $totalNontaxableBonus->toScale(2)->toString(),
            'total_taxable_from_bonus' => $totalTaxableFromBonus->toScale(2)->toString(),
            'total_tax_withheld' => $totalTaxWithheld->toScale(2)->toString(),
            'remarks' => $externalTaxHistory->remarks,

            'employee' => [
                'number' => $externalTaxHistory->employee_number,
                'full_name' => $externalTaxHistory->employee_full_name,
            ],
        ];
    }
}
