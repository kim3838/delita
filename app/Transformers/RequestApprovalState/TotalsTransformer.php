<?php

namespace App\Transformers\RequestApprovalState;

use App\Models\Hydrations\PendingApprovalStatesTotals;
use League\Fractal\TransformerAbstract;

class TotalsTransformer extends TransformerAbstract
{
    public function transform(PendingApprovalStatesTotals $pendingApprovalStatesTotals): array
    {
        return [
            'attendance_adjustment' => $pendingApprovalStatesTotals->total_pending_attendance_adjustment ?? 0,
            'overtime' => $pendingApprovalStatesTotals->total_pending_overtime ?? 0,
            'leave' => $pendingApprovalStatesTotals->total_pending_leave ?? 0,
            'payroll' => $pendingApprovalStatesTotals->total_pending_payroll ?? 0,
        ];
    }
}
