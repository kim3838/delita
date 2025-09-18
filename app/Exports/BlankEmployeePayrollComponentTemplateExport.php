<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BlankEmployeePayrollComponentTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [];
    }

    public function headings(): array
    {
        return ['Employee Number', 'Payroll Component Code', 'Amount', 'Pay Period', 'Pay Type', 'Pay Frequency'];
    }
}
