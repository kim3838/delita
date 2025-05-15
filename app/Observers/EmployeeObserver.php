<?php

namespace App\Observers;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class EmployeeObserver
{
    private string $numberAttribute = 'number';

    public function creating(Model $employee)
    {
        if(empty($employee->{$this->numberAttribute})){
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

            $employee->{$this->numberAttribute} = $number;
        }

        return true;
    }
}
