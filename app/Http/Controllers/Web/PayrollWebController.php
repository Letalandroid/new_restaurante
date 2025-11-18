<?php

namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Models\GeneralHoliday;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Gate;
class PayrollWebController extends Controller{
    public function index(): Response{
        Gate::authorize('viewAny',arguments: GeneralHoliday::class);
        return Inertia::render('panel/Payroll/indexPayroll');
    }
}
