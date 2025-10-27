<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'Cliente_name' => $this->customer->name . ' ' . $this->customer->lastname,
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'number_people' => $this->number_people,
            'date' => Carbon::parse($this->date)->format('d-m-Y'),
            'hour' => Carbon::parse($this->hour)->format('H:i'),
            'reservation_code' => $this->reservation_code,
            'creacion' => Carbon::parse($this->created_at)->format('d-m-Y H:i:s A'),
            'actualizacion' => Carbon::parse($this->updated_at)->format('d-m-Y H:i:s A'),
        ];
    }
}