<?php

namespace App\Exports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

class CustomersExport implements FromCollection, WithHeadings, WithMapping, WithCustomCsvSettings
{
    public function collection()
    {
        // Traer todos los clientes con su tipo de cliente
        return Customer::with('clienteType')->orderBy('id', 'asc')->get();
    }

    public function map($customer): array
    {
        return [
            $customer->id,
            $customer->name . ' ' . $customer->lastname,
            $customer->email,
            $customer->phone,
            '="' . $customer->codigo . '"', // FORZAR TEXTO en Excel
            $customer->clienteType->name,
            $customer->state == 1 ? 'Activo' : 'Inactivo',
            $customer->created_at->format('d-m-Y H:i:s'), // Fecha de creación formateada
            $customer->updated_at->format('d-m-Y H:i:s')  // Fecha de actualización formateada
        ];
    }
    public function headings(): array
    {
        return [
            'ID',
            'Nombre y Apellido',
            'Correo electrónico',
            'Teléfono',
            'Código',
            'Tipo de Cliente',
            'Estado',
            'Creación',
            'Actualización',
        ];
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ';',   // Usa punto y coma para compatibilidad con Excel en español
            'enclosure' => '"',   // Mantiene el texto entre comillas si contiene comas o punto y coma
            'line_ending' => "\n",
            'use_bom' => true,    // Incluye BOM para que Excel muestre bien acentos y ñ
        ];
    }
}
