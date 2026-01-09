<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\PermissionRepository;
use App\Concrete\PermissionService;
use App\Facades\ResponseJson;
use Database\Seeders\PermissionAndRoleSeeder;

class PermissionController extends Controller
{
    public function __construct(
        protected readonly PermissionRepository $repository,
        protected readonly PermissionAndRoleSeeder $permissionAndRoleSeeder,
    ){}

    public function series()
    {
        if(request()->expectsJson()){

            $series = collect(PermissionService::$seriesMap)->map(function($permissionSeries){
                return [
                    'name' => $permissionSeries['name'],
                    'permission_group' => collect($permissionSeries['permissions'])->map(function($permission){
                        return [
                            'name' => $permission['readable_name'],
                            'permissions' => array_map(function($permissionAction) use ($permission){
                                return [
                                    'name' => ucfirst($permissionAction),
                                    'value_key' => $this->permissionAndRoleSeeder::permissionSlug($permission['key'], $permissionAction)
                                ];
                            }, $permission['actions'])
                        ];
                    })
                ];
            });

            return ResponseJson::successfulResponse([
                'series' => $series
            ]);
        }

        abort(404);
    }
}
