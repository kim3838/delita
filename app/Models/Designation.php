<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Designation extends Model
{
    protected $fillable = [
        'company_id',
        'name',
    ];

    protected $casts = [
        'id' => 'int',
        'company_id' => 'int',
        'name' => 'string',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
