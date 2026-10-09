<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    protected $table = 'leads';

    protected $fillable = [
        'curp',
        'monto',
        'plazo',
        'correo',
        'telefono',
        'tipo_prestamo',
        'ingreso',
        'laboral',
        'destino',
        'contacto',
        'horario',
        'ip'
    ];
}