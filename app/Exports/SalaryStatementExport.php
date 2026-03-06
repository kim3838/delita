<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SalaryStatementExport implements FromCollection, WithHeadings
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
            'Payroll number',
            'Status',
            'Remarks',
            'Year',
            'Month',
            'Month readable',
            'Pay frequency',
            'Frequency sequence',
            'Start date',
            'End date',
            'Employee #',
            'Name',
            'Statement type',
            'Is paid',
            'Basic gross',
            'Other gross',
            'Taxable',
            'Nontaxable',
            'Contribution',
            'Withholding tax',
            'Deduction',
            'Net',
        ];
    }
}
