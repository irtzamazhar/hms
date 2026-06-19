<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements Auditable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes, AuditableTrait;

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'avatar', 'employee_id',
        'user_type', 'status', 'joining_date', 'is_two_factor_enabled',
        'two_factor_secret', 'last_login_at', 'last_login_ip',
    ];

    protected $hidden = ['password', 'remember_token', 'two_factor_secret'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'joining_date' => 'date',
            'last_login_at' => 'datetime',
            'is_two_factor_enabled' => 'boolean',
        ];
    }

    public function doctor(): HasOne
    {
        return $this->hasOne(Doctor::class);
    }

    public function staff(): HasOne
    {
        return $this->hasOne(Staff::class);
    }

    public function salaryStructure(): HasOne
    {
        return $this->hasOne(SalaryStructure::class)->where('is_current', true)->latestOfMany();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function homeRoute(): string
    {
        return match($this->user_type) {
            'receptionist'   => route('tokens.index'),
            'doctor'         => route('opd.index'),
            'nurse'          => route('patients.index'),
            'pharmacist'     => route('pharmacy.pos'),
            'lab_technician' => route('lab.index'),
            'accountant'     => route('expenses.index'),
            default          => route('dashboard'),
        };
    }

    public function getAvatarUrlAttribute(): string
    {
        return $this->avatar
            ? asset('storage/' . $this->avatar)
            : 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=7F9CF5&background=EBF4FF';
    }
}
