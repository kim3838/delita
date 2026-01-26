<?php

namespace App\Events\Repositories;

use App\Models\LeaveRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LeaveRequestCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public LeaveRequest $leaveRequest
    ){}

}
