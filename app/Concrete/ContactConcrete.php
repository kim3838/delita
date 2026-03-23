<?php

namespace App\Concrete;

use App\Models\Account;
use App\Models\Employee;
use App\Models\EmployeeContact;
use App\Models\User;

class ContactConcrete
{
    public function isEmailTaken(string $email, $exceptEmployeeId = null): bool
    {
        $employee = Employee::query()->find($exceptEmployeeId);

        $employeeContactQueryBuilder = EmployeeContact::query()
            ->when($exceptEmployeeId, function ($query) use ($exceptEmployeeId) {
                $query->where('employee_id', '!=', $exceptEmployeeId);
            })
            ->where(function ($query) use ($email) {
                $query->where('office_email', $email)
                    ->orWhere('personal_email', $email);
            });

        $emailTakenAsEmployeeEmail = $employeeContactQueryBuilder->exists();

        $userQueryBuilder = User::query()
            ->when(!empty($employee?->user_id), function ($query) use ($employee) {
                $query->where('id', '!=', $employee->user_id);
            })
            ->where('email', $email);

        $emailTakenAsUserEmail = $userQueryBuilder->exists();

        return $emailTakenAsEmployeeEmail || $emailTakenAsUserEmail;
    }

    public function isEmailTakenAsAccountEmail(string $email, $exceptAccountId = null): bool
    {
        $accountQueryBuilder = Account::query()
            ->when($exceptAccountId, function ($query) use ($exceptAccountId) {
                $query->where('id', '!=', $exceptAccountId);
            })
            ->where('email', $email);

        return $accountQueryBuilder->exists();
    }

    public function isPhoneTaken(string $phone, $exceptEmployeeId = null): bool
    {
        $queryBuilder = EmployeeContact::query()
            ->when($exceptEmployeeId, function ($query) use ($exceptEmployeeId) {
                $query->where('employee_id', '!=', $exceptEmployeeId);
            })
            ->where(function ($query) use ($phone) {
                $query->where('office_phone', $phone)
                    ->orWhere('personal_phone', $phone);
            });

        return $queryBuilder->exists();
    }
}
