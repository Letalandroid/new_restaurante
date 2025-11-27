<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'idCategory',
        'details',
        'idAlmacen',
        'priceSale',
        'quantityUnitMeasure',
        'unitMeasure',
        'state',
        'foto',
    ];

    protected $casts = [
        'priceSale' => 'decimal:2',
        'quantityUnitMeasure' => 'decimal:2',
        'state' => 'boolean',
    ];

    protected $appends = ['stock_quantity']; // Agregar campo calculado

    public function category()
    {
        return $this->belongsTo(Category::class, 'idCategory');
    }

    public function almacen()
    {
        return $this->belongsTo(Almacen::class, 'idAlmacen');
    }
    public function MovementDetail(): HasMany {
        return $this->hasMany(MovementInputDetail::class, 'idProduct');
    }

    // En el modelo Product.php
public function getStockQuantityAttribute()
{
    // Contar registros de entrada (idMovementInput no nulo)
    $entradas = $this->MovementDetail()
        ->whereNotNull('idMovementInput')
        ->sum('quantity'); // Aquí SÍ sumamos quantities porque las entradas vienen en lotes

    return $entradas;
}

    public function tieneRelaciones(): bool
    {
        //se agrega todas las relaciones que existan
        return $this->MovementDetail()->exists();
    }
}
