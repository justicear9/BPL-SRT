<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Visit extends Model
{
    protected $fillable = [
        'user_id',
        'customer_id',
        'contact_id',
        'visited_at',
        'comments',
        'visit_latitude',
        'visit_longitude',
    ];

    protected function casts(): array
    {
        return [
            'visited_at' => 'datetime',
            'visit_latitude' => 'float',
            'visit_longitude' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function order(): HasOne
    {
        return $this->hasOne(VisitOrder::class);
    }

    public function samples(): HasMany
    {
        return $this->hasMany(VisitSample::class);
    }

    public function collections(): HasMany
    {
        return $this->hasMany(VisitCollection::class);
    }

    public function orderLineTotal(): float
    {
        $order = $this->relationLoaded('order') ? $this->order : $this->order()->with('lines')->first();
        if (! $order) {
            return 0.0;
        }

        return (float) $order->lines->sum(fn (VisitOrderLine $line) => (float) $line->quantity * (float) $line->unit_price);
    }
}
