<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $table = 'companies';

    protected $fillable = [
        'name',
        'status',
        'logo_path',
        'primary_color',
        'secondary_color',
        'minutes_limit',
        'incoming_did',
        'outbound_callerid',
        'max_concurrent_calls',
        'has_ivr',
    ];
    
    public function users()
    {
        return $this->hasMany(User::class, 'company_id');
    }

    public function extensions()
    {
        return $this->hasManyThrough(UserExtension::class, User::class, 'company_id', 'user_id');
    }   
}