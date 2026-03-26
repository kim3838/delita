<?php

namespace App\Concrete;

class PermissionService
{
    public static array $seriesMap = [
        [
            'name' => 'Admin',
            'permissions' => [
                [
                    'key' => 'user',
                    'readable_name' => 'User',
                    'actions' => ['view', 'create', 'update']
                ],[
                    'key' => 'role',
                    'readable_name' => 'Role',
                    'actions' => ['view', 'create', 'update', 'delete']
                ], [
                    'key' => 'company',
                    'readable_name' => 'Company',
                    'actions' => ['view', 'create', 'update']
                ],
            ]
        ],
        [
            'name' => 'Workforce',
            'permissions' => [
                [
                    'key' => 'employee',
                    'readable_name' => 'Employee',
                    'actions' => ['view', 'create', 'update']
                ],[
                    'key' => 'employee-group',
                    'readable_name' => 'Employee group',
                    'actions' => ['view', 'create', 'update', 'delete']
                ],[
                    'key' => 'employee-employment-profile',
                    'readable_name' => 'Employee employment profile',
                    'actions' => ['view', 'create', 'update', 'delete']
                ],[
                    'key' => 'employee-identification',
                    'readable_name' => 'Employee identification',
                    'actions' => ['view', 'create', 'update', 'delete']
                ],[
                    'key' => 'department',
                    'readable_name' => 'Department',
                    'actions' => ['view', 'create', 'update', 'delete']
                ],[
                    'key' => 'designation',
                    'readable_name' => 'Designation',
                    'actions' => ['view', 'create', 'update', 'delete']
                ],[
                    'key' => 'attendance',
                    'readable_name' => 'Attendance',
                    'actions' => ['view', 'create', 'update', 'delete']
                ],[
                    'key' => 'overtime',
                    'readable_name' => 'Overtime',
                    'actions' => ['view', 'create', 'update', 'delete']
                ],[
                    'key' => 'leave',
                    'readable_name' => 'Leave',
                    'actions' => ['view', 'create', 'delete']
                ]
            ]
        ],
        [
            'name' => 'Policies',
            'permissions' => [
                [
                    'key' => 'shift',
                    'readable_name' => 'Shift',
                    'actions' => ['view', 'create', 'update', 'delete']
                ],[
                    'key' => 'employee-shift-assignment',
                    'readable_name' => 'Employee shift assignment',
                    'actions' => ['view', 'create', 'update', 'delete']
                ],[
                    'key' => 'leave-type',
                    'readable_name' => 'Leave type',
                    'actions' => ['view', 'create', 'update', 'delete']
                ],[
                    'key' => 'employee-leave-type-assignment',
                    'readable_name' => 'Employee leave type assignment',
                    'actions' => ['view', 'create', 'update', 'delete']
                ],[
                    'key' => 'leave-balance-adjustment',
                    'readable_name' => 'Leave balance adjustment',
                    'actions' => ['view', 'create', 'update', 'delete']
                ],[
                    'key' => 'holiday',
                    'readable_name' => 'Holiday',
                    'actions' => ['view', 'create', 'update', 'delete']
                ],
            ]
        ],
        [
            'name' => 'Payroll',
            'permissions' => [
                [
                    'key' => 'payroll-component',
                    'readable_name' => 'Payroll component',
                    'actions' => ['view']
                ],[
                    'key' => 'employee-payroll-component',
                    'readable_name' => 'Employee payroll component',
                    'actions' => ['view', 'create', 'update', 'delete']
                ],[
                    'key' => 'payroll-frequency',
                    'readable_name' => 'Payroll frequency',
                    'actions' => ['view', 'update']
                ],[
                    'key' => 'payroll',
                    'readable_name' => 'Payroll',
                    'actions' => ['view', 'create', 'delete']
                ],[
                    'key' => 'external-tax-history',
                    'readable_name' => 'External tax history',
                    'actions' => ['view', 'create', 'update', 'delete']
                ],[
                    'key' => 'salary-statement',
                    'readable_name' => 'Salary statement',
                    'actions' => ['view', 'update', 'delete', 'export']
                ],
            ]
        ],
        [
            'name' => 'Request & Approval',
            'permissions' => [
                [
                    'key' => 'approval-setting',
                    'readable_name' => 'Approval setting',
                    'actions' => ['view', 'update']
                ],[
                    'key' => 'any-request',
                    'readable_name' => 'Any request',
                    'actions' => ['approve', 'decline']
                ],[
                    'key' => 'approval-states',
                    'readable_name' => 'Approval States',
                    'actions' => ['view']
                ],[
                    'key' => 'attendance-adjustment-request',
                    'readable_name' => 'Attendance adjustment request',
                    'actions' => ['view', 'create', 'delete']
                ],[
                    'key' => 'overtime-request',
                    'readable_name' => 'Overtime request',
                    'actions' => ['view', 'create', 'delete']
                ],[
                    'key' => 'leave-request',
                    'readable_name' => 'Leave request',
                    'actions' => ['view', 'create', 'delete']
                ],[
                    'key' => 'payroll-request',
                    'readable_name' => 'Payroll request',
                    'actions' => ['view', 'create', 'delete']
                ],
            ]
        ],
        [
            'name' => 'Reports',
            'permissions' => [
                [
                    'key' => 'leave-running-balance',
                    'readable_name' => 'Leave running balance',
                    'actions' => ['view']
                ]
            ]
        ],
    ];
}
