<?php

namespace App\Models\Hydrations;

use Illuminate\Database\Eloquent\Model;

class PendingApprovalStatesTotals extends Model
{
    protected $casts = [
        'total_pending_attendance_adjustment' => 'int',
        'total_pending_overtime' => 'int',
        'total_pending_leave' => 'int',
        'total_pending_payroll' => 'int',
    ];
}
