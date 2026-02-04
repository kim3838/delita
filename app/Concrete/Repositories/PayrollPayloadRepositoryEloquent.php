<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\PayrollPayloadRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\Hydrations\Payroll\PayrollPayload;

class PayrollPayloadRepositoryEloquent extends BaseRepositoryEloquent implements PayrollPayloadRepository
{
    public function model(): string
    {
        return PayrollPayload::class;
    }
}
