<?php

namespace App\Events\Repositories;

use App\Models\Company;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CompanyCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Company $company,
        public User $user
    ){}
}
