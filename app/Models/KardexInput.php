<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;  // Agrega esta línea para usar DB

class KardexInput extends Model
{
    use HasFactory;
    protected $table = 'kardex_inputs'; 

    protected $fillable = [
        'idUser',
        'idInput',
        'idProduct',
        'idMovementInput',
        'movement_type',
        'totalPrice',

    ];

   public function user()
{
    return $this->belongsTo(User::class, 'idUser');
}

   public function movementInput()
{
    return $this->belongsTo(MovementInput::class, 'idMovementInput');
}

   public function Input()
{
    return $this->belongsTo(Input::class, 'idInput');
}

   public function Product()
{
    return $this->belongsTo(Product::class, 'idProduct');
}
  
public function getQuantity()
    {
        $query = DB::table('detail_movements_inputs')
            ->where('idMovementInput', $this->idMovementInput);

        // Si es un Input, buscar por idInput
        if ($this->idInput) {
            $query->where('idInput', $this->idInput);
        }
        // Si es un Product, buscar por idProduct
        elseif ($this->idProduct) {
            $query->where('idProduct', $this->idProduct);
        }

        return $query->sum('quantity');
    }
}
