<?php

namespace App\Transformers\Role;

use App\Models\Role;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(Role $role): array
    {
        return [
            'id' => $role->id,
            'ulid' => $role->ulid,
            'account_id' => $role->account_id,
            'name' => $role->name
        ];
    }
}
