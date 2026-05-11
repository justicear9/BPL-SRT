<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    /**
     * Sales reps and managers who can own customers and appear in field-team filters.
     *
     * @return Builder<User>
     */
    public static function assignableFieldTeam(): Builder
    {
        return static::query()
            ->whereIn('role', [UserRole::SalesRep, UserRole::Manager])
            ->orderBy('name');
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    /**
     * @return HasMany<Customer, User>
     */
    public function assignedCustomers(): HasMany
    {
        return $this->hasMany(Customer::class, 'assigned_user_id');
    }

    public function isSalesRep(): bool
    {
        return ($this->role ?? UserRole::SalesRep) === UserRole::SalesRep;
    }

    public function isAdmin(): bool
    {
        return ($this->role ?? null) === UserRole::Admin;
    }

    public function canManageAllVisits(): bool
    {
        return $this->role?->canManageAllVisits() ?? false;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'admin';
    }

    /**
     * Avatar image for Vuexy navbar (no photo upload yet — deterministic placeholder).
     */
    protected function profilePhotoUrl(): Attribute
    {
        return Attribute::get(function (): string {
            $id = (int) ($this->id ?? 0);
            if ($id < 1) {
                return asset('assets/img/avatars/1.png');
            }
            $index = (($id - 1) % 9) + 1;

            return asset('assets/img/avatars/'.$index.'.png');
        });
    }
}
