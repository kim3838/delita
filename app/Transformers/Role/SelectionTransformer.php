<?php

namespace App\Transformers\Role;

use App\Models\Role;
use League\Fractal\TransformerAbstract;

class SelectionTransformer extends TransformerAbstract
{
    public function transform(Role $role): array
    {
        return [
            'value' => $role->id,
            'text' => $role->name,
        ];
    }
}
