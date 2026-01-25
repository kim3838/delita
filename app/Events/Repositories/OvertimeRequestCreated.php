<?php

namespace App\Events\Repositories;

use App\Models\OvertimeRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OvertimeRequestCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public OvertimeRequest $overtimeRequest
    ){}

}
