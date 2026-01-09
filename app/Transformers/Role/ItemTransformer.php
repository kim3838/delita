<?php

namespace App\Transformers\Role;

use App\Facades\Fractal;
use App\Models\Role;
use League\Fractal\TransformerAbstract;

class ItemTransformer extends TransformerAbstract
{
    public function transform(Role $role): array
    {
        return [
            ...Fractal::item($role, ListTransformer::class)
        ];
    }
}
