<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithCustomStartCell
{
    public function collection()
    {
        return Product::orderBy('id', 'asc')->get();  // Traemos todas los productos ordenadas por ID
    }

    public function map($product): array
    {
        return [
            $product->id,
            $product->name,
            $product->details,
            $product->category->name ?? 'Sin categoría',
            $product->almacen->name ?? 'Sin almacén',
            $product->stock_quantity ?? 0,
            number_format($product->priceSale, 2),
            $product->quantityUnitMeasure,
            $product->unitMeasure,
            $product->state == 1 ? 'Activo' : 'Inactivo',
            $product->created_at->format('d-m-Y H:i:s'),
            $product->updated_at->format('d-m-Y H:i:s'),
        ];
    }

    public function headings(): array
    {
        // Encabezados con una fila de título y espaciado
        return [
            ['LISTA DE PRODUCTOS', '', '', '', '', '', '', '', '', '', '', ''], // Fila 1
            [], // Fila 2 en blanco
            [
                'ID', 'Nombre', 'Detalles', 'Categoría', 'Almacén',
                'Stock', 'Precio Venta', 'Cant. Medida', 'Unidad Medida',
                'Estado', 'Creación', 'Actualización'
            ] // Fila 3
        ];
    }

    public function startCell(): string
    {
        return 'A1';
    }

    public function styles(Worksheet $sheet)
    {
        // Combinar celdas para el título
        $sheet->mergeCells('A1:L1');
        $sheet->getStyle('A1:L1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'CFE2F3'], // Azul claro
            ],
        ]);

        // Estilo para los encabezados de la tabla
        $sheet->getStyle('A3:L3')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D9EAD3'], // Verde claro
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ]);

        // Estilo para las filas de datos
        $sheet->getStyle('A4:L' . $sheet->getHighestRow())->applyFromArray([
            'alignment' => [
                'horizontal' => 'center',
                'vertical' => 'center',
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ]);

        // Ajustar el ancho de columnas automáticamente
        foreach (range('A', 'L') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        return [];
    }
}
