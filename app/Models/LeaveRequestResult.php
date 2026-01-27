<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequestResult extends Model
{
    protected $fillable = [
        'leave_request_id',
        'date',
        'successful',
        'remarks'
    ];

    protected $casts = [
        'id' => 'int',
        'leave_request_id' => 'int',
        'date' => 'date',
        'successful' => 'boolean',
        'remarks' => 'string'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function leaveRequest(): BelongsTo
    {
        return $this->belongsTo(LeaveRequest::class);
    }
}
