<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ContributionExport extends Statementable implements FromCollection, WithHeadings
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
        return array_merge(
            parent::headings(),
            [
                'Employee contribution',
                'Employer share'
            ]
        );
    }
}
