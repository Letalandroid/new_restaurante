<?php

namespace App\Exports;

use App\Models\Payroll;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PayrollsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithCustomStartCell
{
    protected $search;
    protected $paid;
    protected $month;

    /**
     * Constructor para recibir filtros desde el controlador
     */
    public function __construct($search = null, $paid = null, $month = null)
    {
        $this->search = $search;
        $this->paid = $paid;
        $this->month = $month;
    }

    /**
     * 🔹 Obtiene las nóminas aplicando los filtros
     */
    public function collection()
    {
        $query = Payroll::with('employee');

// 🔍 Filtro por búsqueda
if (!empty($this->search)) {
    $search = trim($this->search);
    $query->where(function ($q) use ($search) {
        $q->whereHas('employee', function ($sub) use ($search) {
            $sub->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%'])
                ->orWhereRaw('LOWER(codigo) LIKE ?', ['%' . strtolower($search) . '%']);
        });
        // Si es un número, buscar también por employee_id
        if (is_numeric($search)) {
            $q->orWhere('employee_id', $search);
        }
    });
}

      if (!is_null($this->paid) && $this->paid !== '') {
    if (is_bool($this->paid)) {
        $query->where('paid', $this->paid);
    } else {
        // Si viene string
        $query->where('paid', $this->paid);
    }
}


        // 🗓️ Filtro por mes (basado en start_date)
        if (!empty($this->month)) {
            $query->whereMonth('start_date', $this->month);
        }

        return $query->orderBy('id', 'asc')->get();
    }

    /**
     * 🔹 Mapeo de columnas
     */
    public function map($payroll): array
    {
        return [
            $payroll->id,
            $payroll->employee?->codigo ?? 'N/A',
            $payroll->employee?->name ?? 'N/A',
            optional($payroll->start_date)->format('d-m-Y'),
            optional($payroll->end_date)->format('d-m-Y'),
            number_format($payroll->base_salary, 2),
            $payroll->laborable_days,
            $payroll->days_present,
            $payroll->days_absent,
            $payroll->days_justified,
            number_format($payroll->hours_worked, 2),
            number_format($payroll->overtime_hours, 2),
            number_format($payroll->overtime_payment, 2),
            number_format($payroll->bonuses, 2),
            number_format($payroll->absence_discount, 2),
            number_format($payroll->gross_total, 2),
            number_format($payroll->afp_discount, 2),
            number_format($payroll->essalud_contribution, 2),
            number_format($payroll->net_total, 2),
            $payroll->paid ? 'Pagado' : 'Pendiente',
            optional($payroll->created_at)->format('d-m-Y H:i:s'),
        ];
    }

    /**
     * 🔹 Encabezados del Excel
     */
    public function headings(): array
    {
        return [
            ['LISTA DE NÓMINAS GENERADAS'],
            [],
            [
                'ID',
                'Código Empleado',
                'Nombre Empleado',
                'Inicio',
                'Fin',
                'Sueldo Base',
                'Días Laborables',
                'Días Presente',
                'Días Ausente',
                'Días Justificados',
                'Horas Trabajadas',
                'Horas Extra',
                'Pago Extra',
                'Bonos',
                'Descuento Ausencias',
                'Sueldo Bruto',
                'Descuento AFP',
                'Aporte ESSALUD',
                'Sueldo Neto',
                'Estado',
                'Fecha de Creación',
            ]
        ];
    }

    /**
     * 🔹 Celda inicial (A1)
     */
    public function startCell(): string
    {
        return 'A1';
    }

    /**
     * 🔹 Estilos personalizados del Excel
     */
    public function styles(Worksheet $sheet)
    {
        // Fusionar celdas del título
        $sheet->mergeCells('A1:U1');

        // Título principal
        $sheet->getStyle('A1:U1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '000000']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'CFE2F3'],
            ],
        ]);

        // Encabezados de la tabla
        $sheet->getStyle('A3:U3')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D9EAD3'],
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
            ],
        ]);

        // Estilo para las filas de datos
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle("A4:U{$lastRow}")->applyFromArray([
            'alignment' => [
                'horizontal' => 'center',
                'vertical' => 'center',
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
            ],
        ]);

        // Autoajustar todas las columnas
        foreach (range('A', 'U') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return [];
    }
}
