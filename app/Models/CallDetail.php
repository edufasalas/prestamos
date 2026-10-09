<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallDetail extends Model
{
    use HasFactory;

    protected $table = 'call_details';

    // Desactivamos updated_at ya que la tabla solo tiene created_at según el DESCRIBE
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'extension',
        'type',
        'number',
        'duration',
        'status',
        'uniqueid',
    ];

    protected $casts = [
        'duration' => 'integer',
        'created_at' => 'datetime',
    ];

    /**
     * Relación: Un detalle de llamada pertenece a un usuario.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}