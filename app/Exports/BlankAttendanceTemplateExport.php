<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BlankAttendanceTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [];
    }

    public function headings(): array
    {
        return ['Employee Number', 'Shift Code', 'Date', 'First In', 'Lunch Out', 'Lunch In', 'Last Out'];
    }
}
