<?php

namespace App\Events\Repositories;

use App\Models\AttendanceAdjustmentRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AttendanceAdjustmentRequestCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public AttendanceAdjustmentRequest $attendanceAdjustmentRequest
    ){}

}
