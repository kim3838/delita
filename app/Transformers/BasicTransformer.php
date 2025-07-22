<?php

namespace App\Transformers;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class BasicTransformer extends TransformerAbstract
{
    public function transform(Model $model)
    {
        return [
            'id' => $model->id,
            'ulid' => $model->ulid
        ];
    }
}
