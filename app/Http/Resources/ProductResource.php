<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource{
    public function toArray(Request $request): array{
        return [
            'id' => $this->id,
            'name' => $this->name,
            'details' =>$this->details,
            'idCategory' => $this->idCategory,
            'Categoria_name' => $this->category->name,
            'idAlmacen' => $this->idAlmacen,
            'Almacen_name' =>$this->almacen->name,
            'priceSale' => $this->priceSale,
            'quantityUnitMeasure' => $this->quantityUnitMeasure,
            'unitMeasure' => $this->unitMeasure,
            'stock' => $this->stock,
            'state' => $this->state,
            'foto' => $this->foto ?? 'sin imagen',
            'creacion' => Carbon::parse($this->created_at)->format('d-m-Y H:i:s A'),
            'actualizacion' => Carbon::parse($this->updated_at)->format('d-m-Y H:i:s A'),
      ];
    }
}
