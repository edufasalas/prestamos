<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserExtension extends Model
{
    use HasFactory;

    protected $table = 'user_extensions';

    protected $fillable = [
        'user_id',
        'extension_number',
        'sip_secret',
        'websocket_url',
    ];

    // Ocultar la contraseña SIP por seguridad
    protected $hidden = [
        'sip_secret',
    ];

    // Relación: Pertenece a un usuario de Laravel
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}