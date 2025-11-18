<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use TCPDF;

class ReservationPDFController extends Controller
{
    public function exportPDF()
    {
        // Obtener todas las reservaciones con su cliente
        $reservations = Reservation::with('customer')->orderBy('id', 'asc')->get();

        // Convertir los datos a un arreglo legible
        $reservationsArray = $reservations->map(function ($res) {
            return [
                'id' => $res->id,
                'cliente' => $res->customer ? $res->customer->name . ' ' . $res->customer->lastname : 'Sin cliente',
                'email' => $res->customer->email ?? 'No registrado',
                'telefono' => $res->customer->phone ?? 'No registrado',
                'personas' => $res->number_people,
                'fecha' => optional($res->date)->format('d-m-Y'),
                'hora' => $res->hour,
                'codigo' => $res->reservation_code,
                'estado' => $res->state == 1 ? 'Activo' : 'Inactivo',
            ];
        })->toArray();

        // Crear el objeto TCPDF
        $pdf = new TCPDF();

        $pdf->SetCreator('Laravel TCPDF');
        $pdf->SetAuthor('Laravel');
        $pdf->SetTitle('Lista de Reservaciones');
        $pdf->SetSubject('Reporte de Reservaciones');

        // Márgenes y configuración general
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 10);

        // Quitar encabezado y pie predeterminados
        $pdf->SetHeaderData('', 0, '', '', [0, 0, 0], [255, 255, 255]);
        $pdf->setFooterData([0, 0, 0], [255, 255, 255]);

        // Nueva página
        $pdf->AddPage();

        // Título principal
        $pdf->SetFont('helvetica', 'B', 18);
        $pdf->Cell(0, 20, 'Lista de Reservaciones', 0, 1, 'C');

        // Encabezados de la tabla
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(242, 242, 242);

        $header = ['ID', 'Cliente', 'Correo', 'Teléfono', 'Personas', 'Fecha', 'Hora', 'Código', 'Estado'];
        $widths = [6, 40, 40, 20, 18, 18, 17, 17, 17];

        // Escribir los encabezados
        foreach ($header as $i => $col) {
            $pdf->MultiCell($widths[$i], 8, $col, 1, 'C', 1, 0);
        }
        $pdf->Ln();

        // Cuerpo de la tabla
        $pdf->SetFont('helvetica', '', 8);

        foreach ($reservationsArray as $res) {
            // Verificar salto de página
            if ($pdf->GetY() > 250) {
                $pdf->AddPage();
                $pdf->SetFont('helvetica', 'B', 10);
                $pdf->SetFillColor(242, 242, 242);
                foreach ($header as $i => $col) {
                    $pdf->MultiCell($widths[$i], 8, $col, 1, 'C', 1, 0);
                }
                $pdf->Ln();
                $pdf->SetFont('helvetica', '', 8);
            }

            // Datos de cada fila
            $pdf->MultiCell($widths[0], 8, $res['id'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[1], 8, $res['cliente'], 1, 'L', 0, 0);
            $pdf->MultiCell($widths[2], 8, $res['email'], 1, 'L', 0, 0);
            $pdf->MultiCell($widths[3], 8, $res['telefono'], 1, 'L', 0, 0);
            $pdf->MultiCell($widths[4], 8, $res['personas'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[5], 8, $res['fecha'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[6], 8, $res['hora'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[7], 8, $res['codigo'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[8], 8, $res['estado'], 1, 'C', 0, 0);
            $pdf->Ln();
        }

        // Limpiar buffer previo
        if (ob_get_length()) {
            ob_end_clean();
        }

        // Exportar PDF
        $pdfOutput = $pdf->Output('Reservaciones.pdf', 'S');
        return response($pdfOutput)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="Reservaciones.pdf"');
    }
}
