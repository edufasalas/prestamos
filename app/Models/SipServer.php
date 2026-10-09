<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SipServer extends Model
{
    use HasFactory;

    protected $table = 'sip_servers';

    protected $fillable = [
        'name',
        'websocket_url',
        'sip_domain',
        'description',
        'is_active',
    ];

    // Relación: Un servidor SIP tiene muchas extensiones asociadas
    public function extensions()
    {
        return $this->hasMany(UserExtension::class, 'sip_server_id');
    }
}