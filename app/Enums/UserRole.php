<?php

namespace App\Enums;

enum UserRole: string
{
    case SalesRep = 'sales_rep';
    case Manager = 'manager';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::SalesRep => 'Sales rep',
            self::Manager => 'Manager',
            self::Admin => 'Admin',
        };
    }

    public function canManageAllVisits(): bool
    {
        return $this === self::Manager || $this === self::Admin;
    }

    public function canManageCatalog(): bool
    {
        return $this === self::Manager || $this === self::Admin;
    }
}
