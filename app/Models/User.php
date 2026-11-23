<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_SUPER_ADMIN = 'SUPER_ADMIN';
    public const ROLE_EVENT_MANAGER = 'EVENT_MANAGER';
    public const ROLE_FINANCE = 'FINANCE';
    public const ROLE_GATE_STAFF = 'GATE_STAFF';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function isEventManager(): bool
    {
        return $this->role === self::ROLE_EVENT_MANAGER;
    }

    public function isFinance(): bool
    {
        return $this->role === self::ROLE_FINANCE;
    }

    public function isGateStaff(): bool
    {
        return $this->role === self::ROLE_GATE_STAFF;
    }

    public function canAccessAdmin(): bool
    {
        return in_array($this->role, [self::ROLE_SUPER_ADMIN, self::ROLE_EVENT_MANAGER, self::ROLE_FINANCE], true);
    }

    public function canScan(): bool
    {
        return in_array($this->role, [self::ROLE_SUPER_ADMIN, self::ROLE_EVENT_MANAGER, self::ROLE_GATE_STAFF], true);
    }

    public function canManageFinancials(): bool
    {
        return in_array($this->role, [self::ROLE_SUPER_ADMIN, self::ROLE_FINANCE], true);
    }
}
