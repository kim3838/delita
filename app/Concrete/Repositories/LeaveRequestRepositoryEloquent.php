<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\LeaveRequestRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\LeaveRequest;

class LeaveRequestRepositoryEloquent extends BaseRepositoryEloquent implements LeaveRequestRepository
{
    public function model(): string
    {
        return LeaveRequest::class;
    }
}
