<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;
class PayrollResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
public function toArray($request): array
{
$hourly_rate = $this->employee && $this->employee->empleadoType
? $this->employee->empleadoType->hourly_rate
: ($this->base_salary
? $this->base_salary / ($this->laborable_days * 8)
: ($this->hours_worked > 0 ? $this->gross_total / $this->hours_worked : 0)
);

$startDate = Carbon::parse($this->start_date);
$endDate = Carbon::parse($this->end_date);

// Incluimos días de asistencia
$attendanceDays = $this->employee->attendances()
    ->whereBetween('work_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
    ->with('status')
    ->get()
    ->map(function ($att) {
        $workDate = Carbon::parse($att->work_date);
        return [
            'date' => $workDate->format('Y-m-d'),
            'status' => $att->status ? $att->status->name : 'ausente',
        ];
    });

return [
    'id'                   => $this->id,
    'employee_id'          => $this->employee_id,
    'employee_name'        => $this->employee ? $this->employee->name : null,
    'start_date'           => $startDate->format('Y-m-d'),
    'end_date'             => $endDate->format('Y-m-d'),
    'base_salary'          => $this->base_salary,
    'hourly_rate'          => number_format($hourly_rate, 2, '.', ''),
    'laborable_days'       => $this->laborable_days,
    'days_present'         => $this->days_present,
    'days_absent'          => $this->days_absent,
    'days_justified'       => $this->days_justified,
    'proportional_base'    => $this->proportional_base,
    'absence_discount'     => $this->absence_discount,
    'hours_worked'         => $this->hours_worked,
    'overtime_hours'       => $this->overtime_hours,
    'overtime_payment'     => $this->overtime_payment,
    'bonuses'              => $this->bonuses,
    'gross_total'          => $this->gross_total,
    'afp_discount'         => $this->afp_discount,
    'essalud_contribution' => $this->essalud_contribution,
    'net_total'            => $this->net_total,
    'paid'                 => $this->paid,
    'created_at'           => Carbon::parse($this->created_at)->format('Y-m-d H:i:s'),
    'updated_at'           => Carbon::parse($this->updated_at)->format('Y-m-d H:i:s'),
    'details' => $this->whenLoaded('details', function () {
        return $this->details->map(function ($detail) {
            return [
                'id'      => $detail->id,
                'concept' => $detail->concept,
                'amount'  => $detail->amount,
                'type'    => $detail->type,
            ];
        });
    }),
    'attendance_days' => $attendanceDays,
];

}
}
