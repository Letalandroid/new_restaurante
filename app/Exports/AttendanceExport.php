<?php

namespace App\Exports;

use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $employee;
    protected $status;
    protected $dateFrom;
    protected $dateTo;

    public function __construct($employee = null, $status = null, $dateFrom = null, $dateTo = null)
    {
        $this->employee = $employee;
        $this->status = $status;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }

    public function collection()
    {
        $query = Attendance::with(['employee', 'status'])->orderBy('work_date', 'desc');

        if ($this->employee) {
            $employee = trim($this->employee);
            $query->where(function ($q) use ($employee) {
                $q->whereHas('employee', function ($sub) use ($employee) {
                    $sub->whereRaw('LOWER(codigo) LIKE ?', ['%' . strtolower($employee) . '%'])
                        ->orWhereRaw('LOWER(name) LIKE ?', ['%' . strtolower($employee) . '%']);
                });
                if (is_numeric($employee)) {
                    $q->orWhere('employee_id', $employee);
                }
            });
        }

        if ($this->status) {
            $query->where('status_id', $this->status);
        }

        if ($this->dateFrom) $query->where('work_date', '>=', $this->dateFrom);
        if ($this->dateTo)   $query->where('work_date', '<=', $this->dateTo);

        return $query->get();
    }

    public function map($attendance): array
    {
        $workedHours = $attendance->worked_hours ?? $this->calculateWorkedHours($attendance->check_in, $attendance->check_out);

        return [
            $attendance->id,
            $attendance->employee ? $attendance->employee->name : '—',
            $attendance->work_date,
            $attendance->check_in ?? '—',
            $attendance->check_out ?? '—',
            $attendance->status ? $attendance->status->name : '—',
            $workedHours,
            $attendance->justification ?? '—',
        ];
    }

    private function calculateWorkedHours($checkIn, $checkOut)
    {
        if (!$checkIn || !$checkOut) return 0;
        try {
            $in  = strtotime($checkIn);
            $out = strtotime($checkOut);
            return round(max(0, ($out - $in) / 3600), 2);
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function headings(): array
    {
        $rango = $this->dateFrom && $this->dateTo ? "Del {$this->dateFrom} al {$this->dateTo}" :
                 ($this->dateFrom ? "Desde {$this->dateFrom}" :
                 ($this->dateTo ? "Hasta {$this->dateTo}" : "Todas las asistencias"));

        $empleado = $this->employee ? "Empleado: {$this->employee}" : "Todos los empleados";

        $statusTexts = [
            1 => 'PRESENTE',
            2 => 'TARDE',
            3 => 'FALTA',
            4 => 'JUSTIFICADO',
            5 => 'DÍA LIBRE',
        ];

        $estado = $this->status
            ? "Estado: " . ($statusTexts[$this->status] ?? $this->status)
            : "Todos los estados";

        return [
            ["REPORTE DE ASISTENCIAS ({$rango})"],
            [$empleado, $estado],
            [],
            ['ID', 'Empleado', 'Fecha', 'Entrada', 'Salida', 'Estado', 'Horas trabajadas', 'Justificación']
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Estilos cabeceras
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => 'center'],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'CFE2F3'],
            ],
        ]);

        $sheet->getStyle('A4:H4')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => 'center'],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D9EAD3'],
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
            ],
        ]);

        foreach (range('A', 'H') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);

        // RESUMEN: usar la misma colección (respeta filtros)
        $attendances = $this->collection();

        // Mapa de estados canonizado (claves legibles para la tabla)
        $statusTexts = [
            1 => 'Presente',
            2 => 'Tarde',
            3 => 'Falta',
            4 => 'Justificado',
            5 => 'Día Libre',
        ];

        $resumen = [];

        foreach ($attendances as $a) {
            $name = $a->employee ? $a->employee->name : '—';
            $sid  = $a->status_id ?? ($a->status ? $a->status->id : null);
            $statusKey = $statusTexts[$sid] ?? ($a->status ? $a->status->name : 'Otro');

            // inicializar si no existe
            if (!isset($resumen[$name])) {
                $resumen[$name] = [
                    'Horas' => 0,
                    'Presente' => 0,
                    'Tarde' => 0,
                    'Falta' => 0,
                    'Justificado' => 0,
                    'Día Libre' => 0,
                    'Otro' => 0,
                ];
            }

            // sumar horas
            $hours = $this->calculateWorkedHours($a->check_in, $a->check_out);
            $resumen[$name]['Horas'] += $hours;

            // incrementar contador del estado correspondiente
            if (array_key_exists($statusKey, $resumen[$name])) {
                $resumen[$name][$statusKey]++;
            } else {
                // por si viene un estado no mapeado
                $resumen[$name]['Otro']++;
            }
        }

        // Escribir resumen en la hoja (columna J en adelante)
        $startCol = 'J';
        $startRow = 4;

        $sheet->setCellValue("{$startCol}1", 'RESUMEN DE HORAS Y DÍAS POR EMPLEADO');
        $sheet->mergeCells("{$startCol}1:Q1");
        $sheet->getStyle("{$startCol}1")->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => 'center'],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFF2CC'],
            ],
        ]);

        $headers = ['Empleado', 'Horas Totales', 'Presente', 'Tarde', 'Falta', 'Justificado', 'Día Libre', 'Otro'];
        $sheet->fromArray([$headers], null, "{$startCol}{$startRow}");
        $endCol = chr(ord($startCol) + count($headers) - 1);

        $sheet->getStyle("{$startCol}{$startRow}:{$endCol}{$startRow}")->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => 'center'],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D9EAD3'],
            ],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
        ]);

        $row = $startRow + 1;
        foreach ($resumen as $name => $r) {
            $sheet->fromArray([[
                $name,
                round($r['Horas'], 2),
                $r['Presente'],
                $r['Tarde'],
                $r['Falta'],
                $r['Justificado'],
                $r['Día Libre'],
                $r['Otro'],
            ]], null, "{$startCol}{$row}");
            $row++;
        }

        $sheet->getStyle("{$startCol}{$startRow}:{$endCol}" . ($row - 1))->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
        ]);

        for ($c = ord($startCol); $c <= ord($endCol); $c++) {
            $sheet->getColumnDimension(chr($c))->setAutoSize(true);
        }

        return [];
    }
}
