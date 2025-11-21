<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payroll\StorePayrollRequest;
use App\Http\Requests\Payroll\UpdatePayrollRequest;
use App\Http\Resources\PayrollResource;
use App\Models\Payroll;
use App\Models\SettingPayroll;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Pipeline\Pipeline;
use App\Models\PayrollDetail;
use App\Models\GeneralHoliday;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Models\Employee;

use App\Exports\PayrollsExport;
use Maatwebsite\Excel\Facades\Excel;
class PayrollController extends Controller
{
public function index(Request $request)
{
    Gate::authorize('viewAny', Payroll::class);

    $perPage = $request->input('per_page', 15);
    $search = $request->input('search'); // nombre o código del empleado
    $state = $request->input('paid');    // true/false
    $month = $request->input('month');   // 1-12
    $year = $request->input('year');     // yyyy

    $query = Payroll::with(['details', 'employee']);

    // Filtro por nombre o código de empleado
    if ($search) {
        $query->whereHas('employee', function ($q) use ($search) {
            $q->whereRaw('name::text ILIKE ?', ["%{$search}%"])
              ->orWhereRaw('codigo::text ILIKE ?', ["%{$search}%"]);
        });
    }

    // Filtro por estado de pago
    if (!is_null($state)) {
        $query->where('paid', $state);
    }

    // Filtro por mes
    if ($month) {
        $query->whereMonth('start_date', intval($month));
    }

    // Filtro por año
    if ($year) {
        $query->whereYear('start_date', intval($year));
    }

    return PayrollResource::collection(
        $query->paginate($perPage, ['*'], 'page', $request->input('page', 1))
    );
}




    public function store(StorePayrollRequest $request)
    {
        Gate::authorize('create', Payroll::class);

        $validated = $request->validated();

        $payroll = Payroll::create($validated);

        return response()->json([
            'state' => true,
            'message' => 'Nómina registrada correctamente.',
            'payroll' => $payroll
        ]);
    }

    public function show(Payroll $payroll)
    {
        Gate::authorize('view', $payroll);

        return response()->json([
            'state' => true,
            'message' => 'Nómina encontrada',
            'payroll' => new PayrollResource($payroll),
        ], 200);
    }

    public function update(UpdatePayrollRequest $request, Payroll $payroll)
    {
        Gate::authorize('update', $payroll);

        $validated = $request->validated();

        $payroll->update($validated);

        return response()->json([
            'state' => true,
            'message' => 'Nómina actualizada correctamente.',
            'payroll' => $payroll->refresh()
        ]);
    }

    public function destroy(Payroll $payroll)
    {
        Gate::authorize('delete', $payroll);

        // Aquí puedes agregar validación si la nómina ya fue pagada
        if ($payroll->paid) {
            return response()->json([
                'state' => false,
                'message' => 'No se puede eliminar una nómina ya pagada.'
            ], 400);
        }

        $payroll->delete();

        return response()->json([
            'state' => true,
            'message' => 'Nómina eliminada correctamente.',
        ]);
    }

public function generateEmployeePayroll(Request $request)
{
    $request->validate([
        'employee_id' => 'required|exists:employees,id',
        'month' => 'required|integer|min:1|max:12',
        'year' => 'nullable|integer|min:2000',
    ]);

    $year = $request->input('year', now()->year);
    $month = $request->input('month');
    try {
    $employee = Employee::with('empleadoType')->findOrFail($request->employee_id);
} catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
    return response()->json([
        'success' => false,
        'message' => 'Empleado no encontrado.'
    ], 404);
}

    $settings = SettingPayroll::pluck('value', 'key')->toArray();

    $today = Carbon::today();
    $startDate = Carbon::create($year, $month, 1)->startOfMonth();
    $endDate = Carbon::create($year, $month, 1)->endOfMonth();
    if ($today->between($startDate, $endDate)) {
        $endDate = $today;
    }

     // --- Revisar nómina existente ---
    $lastPayroll = Payroll::where('employee_id', $employee->id)
        ->latest('end_date')
        ->first();

    if ($lastPayroll && !$lastPayroll->paid) {
        // Si la última nómina NO está pagada, se actualiza
        $startDate = $lastPayroll->start_date;
        $endDate = $lastPayroll->end_date->min($today); // no pasar de hoy
        $payroll = $lastPayroll;
    } else {
        // Si no existe nómina o ya está pagada, se crea nueva
        $startDate = $lastPayroll ? $lastPayroll->end_date->copy()->addDay() : Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();
        if ($today->between($startDate, $endDate)) {
            $endDate = $today;
        }

        $payroll = new Payroll();
        $payroll->employee_id = $employee->id;
        $payroll->start_date = $startDate;
        $payroll->end_date = $endDate;
        $payroll->paid = false;
    }

    // --- Tipo de empleado ---
    $type = $employee->empleadoType;
    $baseSalary = (float) ($type->base_salary ?? 0.0);
    $shiftHours = (float) ($type->shift_hours ?? 8);
    $rateOvertime = (float) ($type->rate_overtime ?? 1.5);

    $afpPercentage = isset($settings['afp_percentage']) ? (float) $settings['afp_percentage'] : 12;
    $essaludPercentage = isset($settings['essalud_percentage']) ? (float) $settings['essalud_percentage'] : 9;

    // --- Feriados y días libres ---
    $holidays = GeneralHoliday::where(function ($q) use ($startDate, $endDate) {
        $q->whereBetween('date', [$startDate, $endDate])
          ->orWhere(function ($q2) use ($startDate) {
              $q2->where('is_recurring', true)
                 ->whereMonth('date', $startDate->month);
          });
    })->pluck('date')->map(fn($d) => Carbon::parse($d)->toDateString())->toArray();

    $dayOffs = explode(',', $settings['general_day_offs'] ?? 'sunday');

    // --- Asistencias ---
    $attendances = Attendance::with('status')
        ->where('employee_id', $employee->id)
        ->whereBetween('work_date', [$startDate, $endDate])
        ->get();

    // --- Días laborables ---
    $laborableDays = collect();
    $period = new \DatePeriod($startDate, new \DateInterval('P1D'), $startDate->copy()->endOfMonth()->addDay());
    foreach ($period as $date) {
        $d = Carbon::parse($date);
        $weekday = strtolower($d->format('l'));
        if (!in_array($d->toDateString(), $holidays) && !in_array($weekday, $dayOffs)) {
            $laborableDays->push($d->toDateString());
        }
    }

    $totalLaborableDays = $laborableDays->count();
    $daysElapsed = $laborableDays->filter(fn($d) => Carbon::parse($d)->lte($endDate))->count();

    $daysPresent = $attendances->filter(fn($a) => in_array(strtolower($a->status->name ?? ''), ['presente','tarde']))->count();
    $daysAbsent = $attendances->filter(fn($a) => strtolower($a->status->name ?? '') === 'ausente')->count();
    $daysJustified = $attendances->filter(fn($a) => strtolower($a->status->name ?? '') === 'justificado')->count();
    $daysFree = $attendances->filter(fn($a) => strtolower($a->status->name ?? '') === 'día_libre')->count();




// --- Validar que haya días asistidos ---
if ($daysPresent === 0) {
    return response()->json([
        'success' => false,
        'message' => 'No se puede generar nómina: el empleado no tiene días asistidos en este período.'
    ], 422);
}


    // --- Horas trabajadas ---
    $totalMinutesWorked = 0;
    $totalExtraMinutes = 0;
    foreach ($attendances as $a) {
        $status = strtolower($a->status->name ?? '');
        if (in_array($status, ['presente','tarde']) && $a->worked_hours) {
            [$h,$m] = explode(':', $a->worked_hours);
            $workedMinutes = ((int)$h*60)+(int)$m;
            $totalMinutesWorked += $workedMinutes;
            if (($workedMinutes/60) > $shiftHours) {
                $totalExtraMinutes += (($workedMinutes/60) - $shiftHours) * 60;
            }
        }
    }

    $totalHoursWorked = floor($totalMinutesWorked/60);
    $remainingMinutes = $totalMinutesWorked%60;
    $formattedWorkedHours = sprintf('%02d:%02d',$totalHoursWorked,$remainingMinutes);

    $extraHours = floor($totalExtraMinutes/60);
    $extraMinutes = $totalExtraMinutes%60;
    $formattedExtraHours = sprintf('%02d:%02d',$extraHours,$extraMinutes);

if ($type->payment_type === 'fijo') {
    // --- lógica que ya tienes para sueldo base ---
    $dailyRate = $totalLaborableDays>0 ? $baseSalary/$totalLaborableDays : 0;
    $hourlyRate = ($totalLaborableDays>0 && $shiftHours>0) ? ($baseSalary/$totalLaborableDays)/$shiftHours : 0;

    $proportionalBase = $dailyRate * $daysElapsed;
    $absenceDiscount = $dailyRate * $daysAbsent;
    $overtimePay = $hourlyRate * $rateOvertime * ($totalExtraMinutes/60);
    $grossSalary = $proportionalBase - $absenceDiscount + $overtimePay;

} elseif ($type->payment_type === 'por_hora') {
    // --- nuevo cálculo por horas trabajadas ---
    $hourlyRate = (float) ($type->hourly_rate ?? 0); // debes tener columna hourly_rate
    $overtimePay = $hourlyRate * $rateOvertime * ($totalExtraMinutes/60);
    $grossSalary = $hourlyRate * ($totalMinutesWorked/60) + $overtimePay;
    $proportionalBase = $hourlyRate * ($totalMinutesWorked/60); // para guardar en payroll
    $absenceDiscount = 0; // no aplica si se paga solo por horas
}




    $afpDiscount = $afpPercentage/100*$grossSalary;
    $essaludContribution = $essaludPercentage/100*$grossSalary;
    $netSalary = $grossSalary - $afpDiscount;
    
    $punctualityBonus = 0;
    if ($daysAbsent === 0 && $type->has_punctuality_bonus) {
        $punctualityBonus = (float) ($type->punctuality_bonus ?? 0);
        $grossSalary += $punctualityBonus;
        $netSalary += $punctualityBonus;
    }

    // --- Guardar payroll ---
    $payroll->base_salary = $baseSalary;
    $payroll->laborable_days = $totalLaborableDays;
    $payroll->days_present = $daysPresent;
    $payroll->days_absent = $daysAbsent;
    $payroll->days_justified = $daysJustified;
    $payroll->hours_worked = $totalMinutesWorked/60;
    $payroll->overtime_hours = $totalExtraMinutes/60;
    $payroll->overtime_payment = $overtimePay;
    $payroll->absence_discount = $absenceDiscount;
    $payroll->proportional_base = $proportionalBase;
    $payroll->bonuses = $punctualityBonus;
    $payroll->gross_total = $grossSalary;
    $payroll->afp_discount = $afpDiscount;
    $payroll->essalud_contribution = $essaludContribution;
    $payroll->net_total = $netSalary;

    $payroll->save();

    // --- Guardar detalles ---
    $details = [
        [
            'payroll_id' => $payroll->id,
            'concept' => 'Horas extra',
            'amount' => $overtimePay,
            'type' => 'ingreso',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'payroll_id' => $payroll->id,
            'concept' => 'Descuento por ausencias',
            'amount' => $absenceDiscount,
            'type' => 'descuento',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'payroll_id' => $payroll->id,
            'concept' => 'AFP',
            'amount' => $afpDiscount,
            'type' => 'descuento',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ];

    if ($punctualityBonus > 0) {
        $details[] = [
            'payroll_id' => $payroll->id,
            'concept' => 'Bono de puntualidad',
            'amount' => $punctualityBonus,
            'type' => 'ingreso',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
// Limpiar antiguos detalles si existe
$payroll->details()->delete();

// Insertar nuevos detalles
$payroll->details()->createMany($details);


    return response()->json([
        'success' => true,
        'payroll_id' => $payroll->id,
        'net_salary' => round($netSalary,2),
        'gross_salary' => round($grossSalary,2),
    ]);
}


public function exportExcel(Request $request)
 {
        $search = $request->input('search');
        $paid   = $request->input('paid');
        $month  = $request->input('month');

        $fileName = 'Nominas_' . now()->format('Y_m_d_His') . '.xlsx';

        return Excel::download(new PayrollsExport($search, $paid, $month), $fileName);
    }


}
