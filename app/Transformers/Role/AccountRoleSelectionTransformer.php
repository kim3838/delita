<?php

namespace App\Transformers\Role;

use App\Models\Role;
use League\Fractal\TransformerAbstract;

class AccountRoleSelectionTransformer extends TransformerAbstract
{
    public function transform(Role $role): array
    {
        return [
            'value' => $role->id,
            'text' => '...'. substr($role->account->number, -8) . ' ' . $role->name,
        ];
    }
}
