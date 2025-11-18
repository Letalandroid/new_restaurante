<?php

namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Gate;
class ReservationWebController extends Controller{
    public function index(): Response{
        Gate::authorize('viewAny', Reservation::class);
        return Inertia::render('panel/Reservaciones/indexReservation');
    }
}
