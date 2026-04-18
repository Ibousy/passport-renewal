<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'nom', 'prenom', 'email', 'telephone', 'cin',
        'date_naissance', 'nationalite', 'adresse', 'ville', 'pays',
        'role', 'is_active', 'password', 'avatar',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_naissance'    => 'date',
        'derniere_connexion' => 'datetime',
        'is_active'         => 'boolean',
        'password'          => 'hashed',
    ];

    // ─── Accesseurs ──────────────────────────────────────────────────────

    public function getNomCompletAttribute(): string
    {
        return "{$this->prenom} {$this->nom}";
    }

    public function getAvatarUrlAttribute(): string
    {
        return $this->avatar
            ? asset('storage/' . $this->avatar)
            : asset('images/default-avatar.png');
    }

    // ─── Scopes ──────────────────────────────────────────────────────────

    public function scopeActifs($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAdmins($query)
    {
        return $query->whereIn('role', ['admin', 'super_admin']);
    }

    public function scopeAgents($query)
    {
        return $query->where('role', 'agent');
    }

    public function scopeStaff($query)
    {
        return $query->whereIn('role', ['admin', 'super_admin', 'agent']);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'super_admin']);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAgent(): bool
    {
        return $this->role === 'agent';
    }

    public function isStaff(): bool
    {
        return in_array($this->role, ['admin', 'super_admin', 'agent']);
    }

    // ─── Relations ────────────────────────────────────────────────────────

    public function demandes()
    {
        return $this->hasMany(Demande::class);
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class);
    }

    public function notifications_app()
    {
        return $this->hasMany(Notification::class);
    }

    public function notificationsNonLues()
    {
        return $this->hasMany(Notification::class)->where('lu', false);
    }
}
