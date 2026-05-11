<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'sku',
        'name',
        'unit_of_measure',
        'item_category_code',
        'default_unit_price',
        'can_be_sampled',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'default_unit_price' => 'decimal:2',
            'can_be_sampled' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function visitOrderLines(): HasMany
    {
        return $this->hasMany(VisitOrderLine::class);
    }

    public function visitSamples(): HasMany
    {
        return $this->hasMany(VisitSample::class);
    }

    /**
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
