<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use TCPDF;

class AttendancePDFController extends Controller
{
    public function exportPDF()
    {
        // Obtener todos los registros de asistencia con relaciones
        $attendances = Attendance::with(['employee', 'status'])
            ->orderBy('work_date', 'desc')
            ->get();

        // Mapeo de datos para el PDF
        $dataArray = $attendances->map(function ($attendance) {
            return [
                'id' => $attendance->id,
                'employee' => $attendance->employee ? $attendance->employee->name : '—',
                'work_date' => $attendance->work_date,
                'check_in' => $attendance->check_in ?? '—',
                'check_out' => $attendance->check_out ?? '—',
                'status' => $attendance->status ? $attendance->status->name : '—',
                'worked_hours' => $attendance->worked_hours ?? '—',
            ];
        })->toArray();

        // Crear objeto TCPDF
        $pdf = new TCPDF();
        $pdf->SetCreator('Laravel TCPDF');
        $pdf->SetAuthor('Laravel');
        $pdf->SetTitle('Reporte de Asistencias');
        $pdf->SetSubject('Lista de Asistencias');

        // Márgenes y formato
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 10);

        // Sin encabezado ni pie de página por defecto
        $pdf->SetHeaderData('', 0, '', '');
        $pdf->setFooterData(array(0, 0, 0), array(255, 255, 255));

        // Agregar página
        $pdf->AddPage();

        // Título
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 15, 'Reporte de Asistencias', 0, 1, 'C');

        // Encabezado de tabla
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(230, 230, 230);

        $header = ['ID', 'Empleado', 'Fecha', 'Entrada', 'Salida', 'Estado', 'Horas'];
        $widths = [10, 40, 25, 25, 25, 35, 20];

        foreach ($header as $i => $col) {
            $pdf->MultiCell($widths[$i], 8, $col, 1, 'C', 1, 0);
        }
        $pdf->Ln();

        // Filas de la tabla
        $pdf->SetFont('helvetica', '', 9);

        foreach ($dataArray as $row) {
            // Salto de página si es necesario
            if ($pdf->GetY() > 260) {
                $pdf->AddPage();
                $pdf->SetFont('helvetica', 'B', 10);
                $pdf->SetFillColor(230, 230, 230);
                foreach ($header as $i => $col) {
                    $pdf->MultiCell($widths[$i], 8, $col, 1, 'C', 1, 0);
                }
                $pdf->Ln();
                $pdf->SetFont('helvetica', '', 9);
            }

            $pdf->MultiCell($widths[0], 8, $row['id'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[1], 8, $row['employee'], 1, 'L', 0, 0);
            $pdf->MultiCell($widths[2], 8, $row['work_date'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[3], 8, $row['check_in'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[4], 8, $row['check_out'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[5], 8, $row['status'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[6], 8, $row['worked_hours'], 1, 'C', 0, 0);
            $pdf->Ln();
        }

        // Limpiar buffer previo si existe
        if (ob_get_length()) {
            ob_end_clean();
        }

        // Salida del PDF
        $pdfOutput = $pdf->Output('Asistencias.pdf', 'S');
        return response($pdfOutput)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="Asistencias.pdf"');
    }
}
