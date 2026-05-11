<?php

namespace App\Models;

use App\Enums\CustomerType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'assigned_user_id',
        'type',
        'name',
        'phone',
        'address_line',
        'city',
        'region',
        'shop_latitude',
        'shop_longitude',
    ];

    protected function casts(): array
    {
        return [
            'type' => CustomerType::class,
            'shop_latitude' => 'float',
            'shop_longitude' => 'float',
        ];
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    public function isAssignedTo(User $user): bool
    {
        return (int) $this->assigned_user_id === (int) $user->id;
    }

    /**
     * @param  Builder<Customer>  $query
     * @return Builder<Customer>
     */
    public function scopeAssignedTo(Builder $query, User $user): Builder
    {
        return $query->where('assigned_user_id', $user->id);
    }
}
