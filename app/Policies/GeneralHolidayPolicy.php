<?php

namespace App\Policies;

use App\Models\GeneralHoliday;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class GeneralHolidayPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('ver feriados');  
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, GeneralHoliday $generalHoliday): bool
    {
        return $user->can('ver feriados');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('crear feriados');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, GeneralHoliday $generalHoliday): bool
    {
        return $user->can('editar feriados');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, GeneralHoliday $generalHoliday): bool
    {
        return $user->can('eliminar feriados');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, GeneralHoliday $generalHoliday): bool
    {
        return true;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, GeneralHoliday $generalHoliday): bool
    {
        return true;
    }
}
