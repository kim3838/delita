<?php

namespace App\Traits;

use Brick\Math\BigDecimal;

trait HasTotals
{
    public function grandTotal($list, $key): array
    {
        $totals = [];
        $listRawTotalsKey = $key;

        foreach($list as $item){

            $rawTotals = $item[$listRawTotalsKey];

            $toTotalKeys = array_keys($rawTotals);

            foreach($toTotalKeys as $toTotalKey){

                $value = BigDecimal::of($rawTotals[$toTotalKey]);

                if(isset($totals[$toTotalKey])){
                    $totals[$toTotalKey] = $totals[$toTotalKey]->plus($value);
                } else {
                    $totals[$toTotalKey] = $value;
                }
            }
        }

        return $totals;
    }
}
