<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AttendancePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('ver asistencias');  
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Attendance $asistencias): bool
    {
        return $user->can('ver asistencias');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('crear asistencias');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Attendance $asistencias): bool
    {
        return $user->can('editar asistencias');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Attendance $asistencias): bool
    {
        return $user->can('eliminar asistencias');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Attendance $asistencias): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Attendance $asistencias): bool
    {
        return false;
    }
}
