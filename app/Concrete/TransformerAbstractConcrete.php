<?php

namespace App\Concrete;

use Illuminate\Support\Collection;
use League\Fractal\TransformerAbstract;

abstract class TransformerAbstractConcrete extends TransformerAbstract
{
    protected function collectionSummary(Collection $collection, $pluckable, $emptyLabel = 'None'): array
    {
        $summary = [
            'value' => $emptyLabel,
            'extender' => ''
        ];

        if($collection->count() == 1){
            $summary['value'] = $collection->first()->$pluckable;
        } else if($collection->count() > 1){
            $summary['value'] = $collection->first()->$pluckable;
            $summary['extender'] = ' +' . ($collection->count() - 1) . ' more';
        }

        return $summary;
    }

    protected function arraySummary($array, $emptyLabel = 'None'): array
    {
        $summary = [
            'value' => $emptyLabel,
            'extender' => ''
        ];

        if(count($array) == 1){
            $summary['value'] = $array[0];
        } else if(count($array) > 1){
            $summary['value'] = $array[0];
            $summary['extender'] = ' +' . (count($array) - 1) . ' more';
        }

        return $summary;
    }
}
