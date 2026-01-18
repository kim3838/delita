<?php

namespace App\Concrete;

class ApprovalService
{
    public static array $seriesMap = [
        [
            'model_alias' => 'attendance_adjustment_request',
            'readable_name' => 'Attendance adjustment request',
            'foreign_path' => 'employee_foreign_relation_path',
            'employee_foreign_relation_path' => [
                [
                    'foreign' => 'attendance_id',
                    'model' => 'attendance'
                ],[
                    'foreign' => 'employee_id',
                    'model' => 'employee'
                ],
            ]
        ],[
            'model_alias' => 'overtime_request',
            'readable_name' => 'Overtime request',
            'foreign_path' => 'employee_foreign_relation_path',
            'employee_foreign_relation_path' => [
                [
                    'foreign' => 'attendance_id',
                    'model' => 'attendance'
                ],[
                    'foreign' => 'employee_id',
                    'model' => 'employee'
                ],
            ]
        ],[
            'model_alias' => 'leave_request',
            'readable_name' => 'Leave request',
            'foreign_path' => 'employee_foreign_relation_path',
            'employee_foreign_relation_path' => [
                [
                    'foreign' => 'employee_id',
                    'model' => 'employee'
                ],
            ]
        ],
    ];
}
