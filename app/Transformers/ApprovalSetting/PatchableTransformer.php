<?php

namespace App\Transformers\ApprovalSetting;

use App\Concrete\ApprovalService;
use App\Facades\Fractal;
use App\Models\ApprovalSetting;
use App\Transformers\ApprovalSettingApprover\PatchableTransformer as ApprovalSettingApproverPatchableTransformer;
use League\Fractal\TransformerAbstract;

class PatchableTransformer extends TransformerAbstract
{
    public function transform(ApprovalSetting $model): array
    {
        $requestTitle = collect(new ApprovalService()::$seriesMap)
            ->where('model_alias', $model->request_model)
            ->first()['readable_name'] ?? 'Not found';

        $approvers = Fractal::collection(
            $model->approvers->sortBy('order')->values(),
            ApprovalSettingApproverPatchableTransformer::class
        )['data'];

        return [
            'id' => $model->id,
            'company_id' => $model->company_id,
            'request_title' => $requestTitle,
            'employable' => $model->employable,
            'approvers' => $approvers,
        ];
    }
}
