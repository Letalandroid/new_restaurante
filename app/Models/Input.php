<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Input extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'priceBuy',
        'priceSale',
        'idAlmacen',
        'description',
        'state',
        'unitMeasure',
        'quantityUnitMeasure',
    ];

    public function almacen()
    {
        return $this->belongsTo(Almacen::class, 'idAlmacen');
    }

    // Relación muchos a muchos con Plato
    public function dishes()
    {
        return $this->belongsToMany(Dishes::class, 'dish_input', 'input_id', 'dish_id');
    }
    public function MovementDetail(): HasMany {
        return $this->hasMany(MovementInputDetail::class, 'idInput');
    }
    public function tieneRelaciones(): bool
    {
        //se agrega todas las relaciones que existan
        return $this->MovementDetail()->exists()
            || $this->dishes()->exists();
    }
}
