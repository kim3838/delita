<?php

namespace App\Traits;

trait HasOrderable
{
    function order($orderable)
    {
        foreach ($orderable as $key => &$item) {
            $item['order'] = $key + 1;
        }

        unset($item);

        return $orderable;
    }
}
