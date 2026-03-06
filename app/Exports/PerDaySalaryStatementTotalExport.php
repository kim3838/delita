<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PerDaySalaryStatementTotalExport implements FromCollection, WithHeadings
{
    public function __construct(
        public Collection $data
    ){}

    public function collection(): Collection
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'Employee #',
            'Name',
            'Date',
            'Date readable',
            'Weekday',
            'Status',
            'Day type',
            'Formulable type',
            'Component type',
            'Payroll item',

            'Regular pay',
            'Night differential pay',
            'Rest day pay',
            'Total',
        ];
    }
}
