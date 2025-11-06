<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class PublicReservationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'Cliente' => $this->customer->name . ' ' . $this->customer->lastname,
            'date' => Carbon::parse($this->date)->format('d-m-Y'),
            'hour' => Carbon::parse($this->hour)->format('H:i'),
            'message' => 'Reservación registrada correctamente. Pronto recibirá un correo de confirmación.'
        ];
    }
}
