<?php

namespace App\Transformers\ShiftAssignment;

use App\Models\Hydrations\Employee\ShiftAssignment;
use League\Fractal\TransformerAbstract;

class SelectionTransformer extends TransformerAbstract
{
    public function transform(ShiftAssignment $shiftAssignment): array
    {
        $assignmentReadable = $shiftAssignment->shift_stated_shift_end_date
            ? $shiftAssignment->shift_start_date?->format('Y-m-d') . " to " . $shiftAssignment->shift_end_date?->format('Y-m-d')
            : $shiftAssignment->shift_start_date?->format('Y-m-d') . " onwards.";

        return [
            'value' => $shiftAssignment->shift_id,
            'text' => $shiftAssignment->shift_code . PHP_EOL . $shiftAssignment->shift_name . PHP_EOL . $assignmentReadable
        ];
    }
}
