<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable = [
        'iso2',
        'name',
        'phone_code',
        'iso3',
        'region',
        'subregion',
    ];

    protected $casts = [
        'id' => 'int',
        'iso2' => 'string',
        'name' => 'string',
        'phone_code' => 'string',
        'iso3' => 'string',
        'region' => 'string',
        'subregion' => 'string',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function companies()
    {
        return $this->hasMany(Company::class);
    }
}
