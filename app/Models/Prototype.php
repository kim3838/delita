<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prototype extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'type',
        'category',
        'capacity',
        'datetime_added'
    ];
}
