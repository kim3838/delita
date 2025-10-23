<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\AttendanceDetailRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\AttendanceDetail;

class AttendanceDetailRepositoryEloquent extends BaseRepositoryEloquent implements AttendanceDetailRepository
{
    public function model(): string
    {
        return AttendanceDetail::class;
    }
}
