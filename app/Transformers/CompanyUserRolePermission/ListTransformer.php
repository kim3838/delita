<?php

namespace App\Transformers\CompanyUserRolePermission;

use App\Concrete\TransformerAbstractConcrete;
use App\Models\Hydrations\User\CompanyUserRolePermission;

class ListTransformer extends TransformerAbstractConcrete
{
    public function transform(CompanyUserRolePermission $model): array
    {
        return [
            'permission' => $model->permission,
            'permitted' => $model->permitted,
        ];
    }
}
