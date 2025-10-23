<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\AttendanceRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\Attendance;

class AttendanceRepositoryEloquent extends BaseRepositoryEloquent implements AttendanceRepository
{
    public function model(): string
    {
        return Attendance::class;
    }
}
