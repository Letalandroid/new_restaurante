<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Http\Requests\Attendance\StoreAttendanceRequest;
use App\Http\Requests\Attendance\UpdateAttendanceRequest;
use App\Http\Resources\AttendanceResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Pipeline\Pipeline;
use Maatwebsite\Excel\Facades\Excel;
use App\Pipelines\FilterByEmployeeName;
use App\Pipelines\FilterByEmployeeDate;
use App\Pipelines\FilterByStateEmployeeAttendance;

class AttendanceController extends Controller
{
public function index(Request $request)
{
    Gate::authorize('viewAny', Attendance::class);

    $perPage = $request->input('per_page', 15);
    $search = $request->input('search');
    $state = $request->input('state');
    $date = $request->input('date'); // Fecha exacta
    $dateFrom = $request->input('date_from'); // Fecha inicio de rango
    $dateTo = $request->input('date_to'); // Fecha fin de rango

    $query = app(Pipeline::class)
        ->send(Attendance::query())
        ->through([
            new FilterByEmployeeName($search),
            new FilterByStateEmployeeAttendance($state),
            new FilterByEmployeeDate($date, $dateFrom, $dateTo), // Nuevo filtro
        ])
        ->thenReturn();

    return AttendanceResource::collection($query->paginate($perPage));
}
    public function store(StoreAttendanceRequest $request)
        {
            Gate::authorize('create', Attendance::class);
            $validated = $request->validated();

            // Verificar si ya existe asistencia para ese empleado y fecha
            $exists = Attendance::where('employee_id', $validated['employee_id'])
                ->whereDate('work_date', $validated['work_date'])
                ->exists();

            if ($exists) {
                return response()->json([
                    'errors' => ['attendance' => ['El empleado ya tiene un registro de asistencia en esta fecha.']]
                ], 422);
            }

            // Crear registro
            $attendance = Attendance::create([
                'employee_id' => $validated['employee_id'],
                'work_date' => $validated['work_date'],
                'check_in' => $validated['check_in'], // ✅ ahora se guarda correctamente
                'status_id' => $validated['status_id'], // ✅ corregido
                'justification' => $validated['justification'] ?? null,
            ]);

            return response()->json([
                'state' => true,
                'message' => 'Asistencia registrada correctamente (hora de entrada).',
                'attendance' => $attendance
            ]);
        }




public function show(Attendance $asistencia)
{
    Gate::authorize('view', $asistencia);

    return response()->json([
        'state' => true,
        'message' => 'Asistencia encontrada correctamente.',
        'attendance' => new AttendanceResource($asistencia),
    ], 200);
}


public function update(UpdateAttendanceRequest $request, Attendance $asistencia)
{
    Gate::authorize('update', $asistencia);

    $validated = $request->validated();
    $asistencia->update($validated);
    return response()->json([
        'state' => true,
        'message' => 'Asistencia actualizada correctamente.',
        'attendance' => $asistencia->refresh()
    ]);
}

    public function destroy(Attendance $asistencia)
    {
        Gate::authorize('delete', $asistencia);
        $asistencia->delete();

        return response()->json([
            'state' => true,
            'message' => 'Asistencia eliminada correctamente.',
        ]);
    }

   
}
