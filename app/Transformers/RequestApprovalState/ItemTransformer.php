<?php

namespace App\Transformers\RequestApprovalState;

use App\Facades\Fractal;
use App\Models\RequestApprovalState;
use League\Fractal\TransformerAbstract;

class ItemTransformer extends TransformerAbstract
{
    public function transform(RequestApprovalState $requestApprovalState): array
    {
        return [...Fractal::item($requestApprovalState, BasicListTransformer::class)];
    }
}
