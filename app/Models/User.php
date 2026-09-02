<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = ['password' => 'hashed'];

    // Relasi
    public function stokMasuks()
    {
        return $this->hasMany(StokMasuk::class);
    }

    public function stokKeluars()
    {
        return $this->hasMany(StokKeluar::class);
    }

    // Helper role
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isKasir(): bool
    {
        return $this->role === 'kasir';
    }

    public function isGudang(): bool
    {
        return $this->role === 'gudang';
    }

    // Label role untuk tampilan
    public function getRoleLabelAttribute(): string
    {
        return match($this->role) {
            'admin'  => 'Administrator',
            'kasir'  => 'Kasir',
            'gudang' => 'Bagian Gudang',
            default  => ucfirst($this->role),
        };
    }
}