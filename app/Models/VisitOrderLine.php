<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitOrderLine extends Model
{
    protected $fillable = [
        'visit_order_id',
        'product_id',
        'quantity',
        'unit_price',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
        ];
    }

    public function visitOrder(): BelongsTo
    {
        return $this->belongsTo(VisitOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function lineTotal(): float
    {
        return (float) $this->quantity * (float) $this->unit_price;
    }
}
