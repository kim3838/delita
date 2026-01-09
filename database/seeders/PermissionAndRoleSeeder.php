<?php

namespace Database\Seeders;

use App\Concrete\PermissionService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionAndRoleSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [];

        foreach(PermissionService::$seriesMap as $series){

            foreach($series['permissions'] as $seriesPermission){

                $permissions = array_merge(
                    $permissions,
                    self::permissionSlugs(
                        $seriesPermission['key'],
                        $seriesPermission['actions']
                    )
                );
            }
        }

        foreach ($permissions as $permission) {

            Permission::query()->firstOrCreate([
                'name' => $permission,
            ]);
        }
    }

    public static function permissionSlugs($slug, $actions = []): array
    {
        return array_map(fn($action) => "{$action}-{$slug}", $actions);
    }

    public static function permissionSlug($slug, $action): string
    {
        return "{$action}-{$slug}";
    }
}
