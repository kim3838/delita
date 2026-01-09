<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\PermissionRepository;
use App\Concrete\BaseRepositoryEloquent;
use Spatie\Permission\Models\Permission;

class PermissionRepositoryEloquent extends BaseRepositoryEloquent implements PermissionRepository
{
    public function model(): string
    {
        return Permission::class;
    }
}
