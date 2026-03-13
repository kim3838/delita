<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;

class OrderableObserver
{
    public function creating(Model $model)
    {
        $last = $model
            ->query()
            ->where('company_id', $model->company_id)
            ->orderBy('order', 'DESC')
            ->first();

        $model->order = empty($last) ? 1 : $last->order + 1;

        return true;
    }
}
