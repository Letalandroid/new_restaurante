<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'ruc',
        'phone',
        'state',
    ];

    protected $casts = [
        'state' => 'boolean',
    ];
    public function tieneRelaciones(): bool
    {
        // Verificar solo las relaciones que realmente existen
        // Por ejemplo, si tienes otras tablas relacionadas con suppliers
        return false; // Por ahora no hay relaciones
        
        // O si tienes otras relaciones, verifícalas aquí:
        // return $this->compras()->exists() || $this->productos()->exists();
    }
}
