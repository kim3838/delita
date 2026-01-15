<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\ApprovalSettingApproverRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\ApprovalSettingApprover;

class ApprovalSettingApproverRepositoryEloquent extends BaseRepositoryEloquent implements ApprovalSettingApproverRepository
{
    public function model(): string
    {
        return ApprovalSettingApprover::class;
    }
}
