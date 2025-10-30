<?php

namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Gate;
class AttendancesWebController extends Controller{
    public function index(): Response{
        Gate::authorize('viewAny',Attendance::class);
        return Inertia::render('panel/Attendances/indexAttendance');
    }
}
