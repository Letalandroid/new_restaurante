<?php

namespace App\Exports;

use App\Models\Reservation;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

class ReservationExport implements FromCollection, WithHeadings, WithMapping, WithCustomCsvSettings
{
    public function collection()
    {
        return Reservation::with('customer')->orderBy('id', 'asc')->get();
    }

    public function map($reservation): array
    {
        return [
            $reservation->id,
            $reservation->customer ? $reservation->customer->name . ' ' . $reservation->customer->lastname : 'Sin cliente',
            $reservation->customer->email ?? 'N/A',
            $reservation->customer->phone ?? 'N/A',
            $reservation->number_people,
            optional($reservation->date)->format('d-m-Y'),
            $reservation->hour,
            $reservation->reservation_code,
            $reservation->state == 1 ? 'Activo' : 'Inactivo',
            $reservation->created_at->format('d-m-Y H:i:s'),
            $reservation->updated_at->format('d-m-Y H:i:s'),
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Cliente',
            'Correo electrónico',
            'Teléfono',
            'N° Personas',
            'Fecha',
            'Hora',
            'Código de Reservación',
            'Estado',
            'Creación',
            'Actualización',
        ];
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ';',   // 🔹 Usa punto y coma para separar columnas en Excel español
            'enclosure' => '"',   // 🔹 Asegura que los textos se mantengan entre comillas si contienen comas
            'line_ending' => "\n",
            'use_bom' => true,    // 🔹 Incluye BOM para que Excel reconozca bien los acentos y ñ
        ];
    }
}
