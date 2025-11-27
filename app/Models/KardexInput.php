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
        // ✅ PARA PRODUCTOS EN SALIDAS: Buscar el registro específico
    if ($this->idProduct && $this->idMovementInput === null) {
        // Buscar el MovementInputDetail que se creó al mismo tiempo
        $movementDetail = DB::table('detail_movements_inputs')
            ->where('idProduct', $this->idProduct)
            ->where('idMovementInput', null)
            ->whereBetween('created_at', [
                $this->created_at->subSeconds(30), // 30 segundos antes
                $this->created_at->addSeconds(30)  // 30 segundos después
            ])
            ->first();
        
        // Si encontró el registro, devolver su quantity, sino 1
        return $movementDetail ? $movementDetail->quantity : 1;
    }
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
