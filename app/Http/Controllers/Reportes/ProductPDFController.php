<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use App\Models\Product;
use TCPDF;

class ProductPDFController extends Controller
{
    public function exportPDF()
    {
        // Obtener los datos de los productos y convertirlos en un array para facilitar el manejo
        $products = Product::orderBy('id', 'asc')->get();

        $productsArray = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'details' => $product->details,
                'category_name' => $product->category->name ?? 'Sin categoría',
                'almacen_name' => $product->almacen->name ?? 'Sin almacén',
                'stock' => $product->stock_quantity,
                'priceSale' => number_format($product->priceSale, 2),
                'quantityUnitMeasure' => $product->quantityUnitMeasure,
                'unitMeasure' => $product->unitMeasure,
                'state' => $product->state == 1 ? 'Activo' : 'Inactivo',
                'foto' => $product->foto,
            ];
        })->toArray();

        // Crear el objeto TCPDF
        $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);

        $pdf->SetCreator('Laravel TCPDF');
        $pdf->SetAuthor('Laravel');
        $pdf->SetTitle('Lista de Productos');
        $pdf->SetSubject('Reporte de Productos');

        // Configuración de márgenes
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 10);

        // Quitar encabezado y pie de página predeterminados
        $pdf->SetHeaderData('', 0, '', '', [0, 0, 0], [255, 255, 255]);
        $pdf->setFooterData(array(0, 0, 0), array(255, 255, 255));

        // Agregar una página
        $pdf->AddPage();

        // Título
        $pdf->SetFont('helvetica', 'B', 18);
        $pdf->Cell(0, 15, 'Lista de Productos', 0, 1, 'C');
        $pdf->Ln(5);

        // Encabezados de la tabla
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor(230, 240, 255); // Azul claro para encabezados

        $header = [
            'ID', 'Nombre', 'Detalles', 'Categoría', 'Almacén',
            'Stock', 'Precio Venta', 'Cant. Medida', 'Unidad', 'Estado', 'Foto'
        ];

        $widths = [10, 35, 60, 25, 30, 15, 20, 20, 20, 18, 25];

        foreach ($header as $i => $col) {
            $pdf->MultiCell($widths[$i], 9, $col, 1, 'C', 1, 0);
        }
        $pdf->Ln();

        // Filas
        $pdf->SetFont('helvetica', '', 8);
        $rowHeight = 18;

        foreach ($productsArray as $product) {
            // Si llega al final de la página, agrega una nueva con encabezado
            if ($pdf->GetY() > 180) {
                $pdf->AddPage();
                $pdf->SetFont('helvetica', 'B', 9);
                $pdf->SetFillColor(230, 240, 255);
                foreach ($header as $i => $col) {
                    $pdf->MultiCell($widths[$i], 9, $col, 1, 'C', 1, 0);
                }
                $pdf->Ln();
                $pdf->SetFont('helvetica', '', 8);
            }

            $pdf->MultiCell($widths[0], $rowHeight, $product['id'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[1], $rowHeight, $product['name'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[2], $rowHeight, $product['details'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[3], $rowHeight, $product['category_name'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[4], $rowHeight, $product['almacen_name'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[5], $rowHeight, $product['stock'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[6], $rowHeight, 'S/ ' . $product['priceSale'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[7], $rowHeight, $product['quantityUnitMeasure'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[8], $rowHeight, $product['unitMeasure'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[9], $rowHeight, $product['state'], 1, 'C', 0, 0);

            // Foto
            $imgPath = public_path('storage/uploads/fotos/productos/' . $product['foto']);
            $x = $pdf->GetX();
            $y = $pdf->GetY();

            $pdf->MultiCell($widths[10], $rowHeight, '', 1, 'C', 0, 0);

            if (file_exists($imgPath) && $product['foto'] !== 'sin imagen') {
                $imgWidth = 14;
                $imgHeight = $rowHeight - 4;
                $centerX = $x + ($widths[10] - $imgWidth) / 2;
                $pdf->Image($imgPath, $centerX, $y + 2, $imgWidth, $imgHeight, '', '', '', false, 300);
            } else {
                $pdf->SetXY($x, $y);
                $pdf->MultiCell($widths[10], $rowHeight, 'Sin foto de producto', 1, 'C', 0, 0);
            }

            $pdf->Ln($rowHeight);
        }

        // Limpiar buffer si hay salida previa
        if (ob_get_length()) {
            ob_end_clean();
        }

        // Generar el PDF y devolverlo al navegador
        $pdfOutput = $pdf->Output('Productos.pdf', 'S');
        return response($pdfOutput)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="Productos.pdf"');
    }
}
