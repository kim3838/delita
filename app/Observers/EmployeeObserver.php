<?php

namespace App\Observers;

use App\Events\Repositories\EmployeeCreated;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Str;

class EmployeeObserver
{
    private string $customNumberAttribute = 'sequenced_number';

    public function creating(Employee $employee): bool
    {
        if (empty($employee->ulid)) {
            $employee->ulid = (string) Str::ulid();
        }

        return true;
    }

    public function created(Employee $employee): void
    {
        event(new EmployeeCreated($employee));
    }

    public function addCustomNumberAttribute(Employee $employee): Employee
    {
        $series = 1;

        $dateCreating = Carbon::parse($employee->date_registered);

        $seriesUpToDate = $employee::whereBetween('date_registered', [
            Carbon::parse($dateCreating)->startOfYear()->toDateTimeString(),
            Carbon::parse($dateCreating)->endOfYear()->toDateTimeString()
        ])->count();

        $series = $series + $seriesUpToDate;

        $yearCreating = $dateCreating->format('y');
        $series = str_pad($series,3, '0',STR_PAD_LEFT);
        $prefix = "";

        $number = "{$prefix}{$yearCreating}-{$series}";

        $employee->{$this->customNumberAttribute} = $number;

        return $employee;
    }
}
