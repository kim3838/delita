<?php

namespace App\Exports;

abstract class Statementable
{
    public function headings(): array
    {
        return [
            'Payroll number',
            'Year',
            'Month',
            'Month readable',
            'Employee #',
            'Name',
            'Component name',
        ];
    }
}
