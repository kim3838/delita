<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PayrollExport implements FromCollection, WithHeadings
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
            'Basic gross',
            'Other gross',
            'Taxable',
            'Nontaxable',
            'Contribution',
            'Employer contribution share',
            'Withholding tax',
            'Tax refund',
            'Deduction',
            'Net',
        ];
    }
}
