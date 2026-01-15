<?php

namespace App\Concrete;

class ApprovalService
{
    public static array $seriesMap = [
        [
            'model_alias' => 'attendance_adjustment_request',
            'readable_name' => 'Attendance adjustment request',
        ],[
            'model_alias' => 'overtime_request',
            'readable_name' => 'Overtime request',
        ],[
            'model_alias' => 'leave_request',
            'readable_name' => 'Leave request',
        ],
    ];
}
