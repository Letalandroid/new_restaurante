<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovementInputDetail extends Model
{
    use HasFactory;

    protected $table = 'detail_movements_inputs'; // Especificamos el nombre de la tabla

    protected $fillable = [
        'idMovementInput',
        'idInput',
        'idProduct',
        'quantity',
        'totalPrice',
        'priceUnit',
        'batch',
        'expirationDate',
    ];

    // Definir relaciones con los otros modelos

    /**
     * Relación con el modelo MovementInput
     */
// En el modelo MovementInputDetail
public function movementInput()
{
    // Relación inversa con MovementInput
    return $this->belongsTo(MovementInput::class, 'idMovementInput', 'id'); // Se especifica que la clave foránea es 'idMovementInput'
}

    /**
     * Relación con el modelo Input
     */
    public function input()
    {
        return $this->belongsTo(Input::class, 'idInput');
    }
    // Relación con Product
    public function product()
    {
        return $this->belongsTo(Product::class, 'idProduct');
    }
}
