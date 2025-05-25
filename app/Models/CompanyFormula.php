<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

class CompanyFormula extends Pivot
{
    protected $fillable =[
        'formula_id',
        'company_id',
        'settings'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'settings' => 'object'
    ];

    public function compensations(): HasMany
    {
        return $this->hasMany(Compensation::class);
    }
}
