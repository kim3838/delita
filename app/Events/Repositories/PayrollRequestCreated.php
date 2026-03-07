<?php

namespace App\Events\Repositories;

use App\Models\PayrollRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PayrollRequestCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public PayrollRequest $payrollRequest
    ){}

}
