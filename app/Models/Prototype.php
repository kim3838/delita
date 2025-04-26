<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prototype extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'type',
        'category',
        'capacity',
        'json_data',
        'json_data->foo',
        'datetime_added'
    ];

    protected $casts = [
        'json_data' => 'array',
    ];
}
