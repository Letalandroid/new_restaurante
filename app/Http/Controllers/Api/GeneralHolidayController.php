<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GeneralHolidays\StoreGeneralHolidayRequest;
use App\Http\Requests\GeneralHolidays\UpdateGeneralHolidayRequest;
use App\Http\Resources\GeneralHolidayResource;
use App\Models\GeneralHoliday;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Pipeline\Pipeline;

class GeneralHolidayController extends Controller
{
    public function index(Request $request)
{
    Gate::authorize('viewAny', GeneralHoliday::class);

    $perPage = $request->input('per_page', 15);
    $search = $request->input('search');
    $isRecurring = $request->input('is_recurring'); // <-- nuevo filtro

    $query = GeneralHoliday::query()
        ->when($search, function ($q) use ($search) {
            $q->where(function ($subQuery) use ($search) {
                $subQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('date', 'like', "%{$search}%");
            });
        })
        ->when(isset($isRecurring) && $isRecurring !== '', function ($q) use ($isRecurring) {
            // Asegura que solo filtre si el valor viene explícitamente (0 o 1)
            $q->where('is_recurring', (bool) $isRecurring);
        })
        ->orderBy('date', 'desc');

    return GeneralHolidayResource::collection($query->paginate($perPage));
}


    public function store(StoreGeneralHolidayRequest $request)
    {
        Gate::authorize('create', GeneralHoliday::class);

        $holiday = GeneralHoliday::create($request->validated());

        return response()->json([
            'state' => true,
            'message' => 'Feriado registrado correctamente.',
            'holiday' => new GeneralHolidayResource($holiday),
        ], 201);
    }

    public function show(GeneralHoliday $generalHoliday)
    {
        Gate::authorize('view', $generalHoliday);

        return response()->json([
            'state' => true,
            'holiday' => new GeneralHolidayResource($generalHoliday),
        ]);
    }

    public function update(UpdateGeneralHolidayRequest $request, GeneralHoliday $generalHoliday)
    {
        Gate::authorize('update', $generalHoliday);

        $generalHoliday->update($request->validated());

        return response()->json([
            'state' => true,
            'message' => 'Feriado actualizado correctamente.',
            'holiday' => new GeneralHolidayResource($generalHoliday->refresh()),
        ]);
    }

    public function destroy(GeneralHoliday $generalHoliday)
    {
        Gate::authorize('delete', $generalHoliday);

        $generalHoliday->delete();

        return response()->json([
            'state' => true,
            'message' => 'Feriado eliminado correctamente.',
        ]);
    }
}
