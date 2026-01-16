<?php

namespace App\Transformers\RequestApprovalState;

use App\Models\RequestApprovalState;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(RequestApprovalState $requestApprovalState): array
    {
        return [
            'order' => $requestApprovalState->order,
            'approver' => $requestApprovalState->approver->name,
            'status' => $requestApprovalState->status?->toArray(),
        ];
    }
}
