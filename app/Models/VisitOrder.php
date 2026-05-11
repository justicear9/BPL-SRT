<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VisitOrder extends Model
{
    protected $fillable = [
        'visit_id',
    ];

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(VisitOrderLine::class);
    }

    public function lineTotal(): float
    {
        $this->loadMissing('lines');

        return (float) $this->lines->sum(fn (VisitOrderLine $line) => (float) $line->quantity * (float) $line->unit_price);
    }
}
