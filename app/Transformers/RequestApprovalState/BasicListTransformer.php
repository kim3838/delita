<?php

namespace App\Transformers\RequestApprovalState;

use App\Models\RequestApprovalState;
use League\Fractal\TransformerAbstract;

class BasicListTransformer extends TransformerAbstract
{
    public function transform(RequestApprovalState $requestApprovalState): array
    {
        return [
            'order' => $requestApprovalState->order,
            'approver_id' => $requestApprovalState->approver_id,
            'approved_by' => $requestApprovalState->approved_by,
            'approved_by_username' => $requestApprovalState->approvedBy?->name,
            'remarks' => $requestApprovalState->remarks,
            'status' => $requestApprovalState->status?->toArray(),
            'approved_at' => $requestApprovalState->approved_at,
        ];
    }
}
