<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'company_id', // <--- Añade esto aquí
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Relación: Un usuario pertenece a una empresa (Company/Tenant).
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    
    public function callDetails()
    {
        return $this->hasMany(CallDetail::class, 'user_id');
    }    

    /**
     * Relación: Un usuario puede tener múltiples extensiones SIP.
     */
    public function extensions()
    {
        return $this->hasMany(UserExtension::class, 'user_id');
    }

    /**
     * Relación directa para obtener la extensión SIP activa del usuario.
     */
    public function activeExtension()
    {
        return $this->hasOne(UserExtension::class, 'user_id');
    }

    /**
     * Relación: Un usuario puede tener múltiples sesiones registradas en la DB.
     */
    public function sessions()
    {
        return $this->hasMany(Session::class, 'user_id');
    }

    /**
     * Helper: Verifica si el usuario estuvo activo en los últimos N minutos (por defecto 5).
     */
    public function isOnline(int $minutes = 5): bool
    {
        return $this->sessions()
            ->where('last_activity', '>=', now()->subMinutes($minutes)->timestamp)
            ->exists();
    }
}