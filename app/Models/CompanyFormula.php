<?php

namespace App\Models;

use App\Casts\Parsable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'settings' => Parsable::class
    ];

    public function formula(): BelongsTo
    {
        return $this->belongsTo(Formula::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function compensations(): HasMany
    {
        return $this->hasMany(Compensation::class);
    }
}
