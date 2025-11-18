<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use App\Models\Dishes;
use TCPDF;

class DishPDFController extends Controller
{
    public function exportPDF()
{
    // Obtener los platos con sus insumos
    $dishes = Dishes::with('insumos', 'category')->orderBy('id', 'asc')->get();

    // Crear el objeto TCPDF
    $pdf = new TCPDF();
    $pdf->SetCreator('Laravel TCPDF');
    $pdf->SetAuthor('Laravel');
    $pdf->SetTitle('Lista de Platos');
    $pdf->SetSubject('Reporte de Platos');
    $pdf->SetMargins(10, 10, 10);
    $pdf->SetAutoPageBreak(true, 10);
    $pdf->SetHeaderData('', 0, '', '', [0,0,0],[255,255,255]);
    $pdf->setFooterData([0,0,0],[255,255,255]);
    $pdf->AddPage();

    // Título
    $pdf->SetFont('helvetica', 'B', 18);
    $pdf->Cell(0, 20, 'Lista de Platos', 0, 1, 'C');

    // Encabezados de la tabla
    $header = ['ID','Nombre','Precio','Cantidad','Categoria','Insumos','Estado','Creación'];
    $widths = [7, 35, 20, 18, 27, 50, 15, 25];
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetFillColor(242,242,242);

    foreach ($header as $i => $col) {
        $pdf->MultiCell($widths[$i], 7, $col, 1, 'C', 1, 0);
    }
    $pdf->Ln();

    // Datos
    $pdf->SetFont('helvetica', '', 7);

    foreach ($dishes as $dish) {
        $insumos = $dish->insumos;
        $first = true;

        if ($insumos->isEmpty()) {
            $insumos = collect([ (object)['name' => 'No tiene insumos', 'quantityUnitMeasure' => '', 'unitMeasure' => ''] ]);
        }

        foreach ($insumos as $insumo) {
            if ($pdf->GetY() > 250) {
                $pdf->AddPage();
                // Repetir encabezados
                $pdf->SetFont('helvetica', 'B', 7);
                $pdf->SetFillColor(242,242,242);
                foreach ($header as $i => $col) {
                    $pdf->MultiCell($widths[$i], 7, $col, 1, 'C', 1, 0);
                }
                $pdf->Ln();
                $pdf->SetFont('helvetica', '', 7);
            }

            // Columnas del plato solo en la primera fila de insumos
            if ($first) {
                $pdf->MultiCell($widths[0], 7, $dish->id, 1, 'C', 0, 0);
                $pdf->MultiCell($widths[1], 7, $dish->name, 1, 'C', 0, 0);
                $pdf->MultiCell($widths[2], 7, 'S/. ' . number_format($dish->price,2), 1, 'C', 0, 0);
                $pdf->MultiCell($widths[3], 7, $dish->quantity, 1, 'C', 0, 0);
                $pdf->MultiCell($widths[4], 7, $dish->category->name ?? 'Sin Categoria', 1, 'C', 0, 0);
                $first = false;
            } else {
                // Dejar las columnas del plato en blanco para los insumos adicionales
                for ($i = 0; $i < 5; $i++) {
                    $pdf->MultiCell($widths[$i], 7, '', 1, 'C', 0, 0);
                }
            }

            // Columna insumos
            $pdf->MultiCell($widths[5], 7, "{$insumo->name} ({$insumo->quantityUnitMeasure} {$insumo->unitMeasure})", 1, 'L', 0, 0);

            // Columnas restantes
            $pdf->MultiCell($widths[6], 7, $dish->state == 1 ? 'Activo' : 'Inactivo', 1, 'C', 0, 0);
            $pdf->MultiCell($widths[7], 7, $dish->created_at->format('d-m-Y H:i:s'), 1, 'C', 0, 0);
            $pdf->Ln();
        }
    }

    if (ob_get_length()) {
        ob_end_clean();
    }

    $pdfOutput = $pdf->Output('Platos.pdf', 'S');
    return response($pdfOutput)
        ->header('Content-Type','application/pdf')
        ->header('Content-Disposition','attachment; filename="Platos.pdf"');
}
}
