<?php

namespace App\Listeners;

use App\Events\Repositories\AccountCreated;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

class AccountCreatedChain
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(AccountCreated $event): void
    {
        //Create an admin role that has all permissions
        $adminRole = $event->account->roles()->create([
            'ulid' => Str::ulid(),
            'name' => 'Admin',
            'guard_name' => 'web'
        ]);

        $adminRole->syncPermissions(Permission::all());
    }
}
