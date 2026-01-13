<?php

namespace App\Transformers\ApprovalSettingApprover;

use App\Models\ApprovalSettingApprover;
use League\Fractal\TransformerAbstract;

class PatchableTransformer extends TransformerAbstract
{
    public function transform(ApprovalSettingApprover $model): array
    {
        return [
            'id' => $model->id,
            'approval_setting_id' => $model->approval_setting_id,
            'order' => $model->order,
            'approver' => $model->approver_id
        ];
    }
}
