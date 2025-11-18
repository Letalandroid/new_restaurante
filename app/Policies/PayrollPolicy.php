<?php

namespace App\Policies;

use App\Models\Payroll;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PayrollPolicy
{
    /**
     * Determine whether the user can view any payrolls.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('ver nominas');
    }

    /**
     * Determine whether the user can view a specific payroll.
     */
    public function view(User $user, Payroll $payroll): bool
    {
        return $user->can('ver nominas');
    }

    /**
     * Determine whether the user can create payrolls.
     */
    public function create(User $user): bool
    {
        return $user->can('crear nominas');
    }

    /**
     * Determine whether the user can update a payroll.
     */
    public function update(User $user, Payroll $payroll): bool
    {
        return $user->can('editar nominas');
    }

    /**
     * Determine whether the user can delete a payroll.
     */
    public function delete(User $user, Payroll $payroll): bool
    {
        return $user->can('eliminar nominas');
    }

    /**
     * Determine whether the user can restore a payroll.
     */
    public function restore(User $user, Payroll $payroll): bool
    {
        return true;
    }

    /**
     * Determine whether the user can permanently delete a payroll.
     */
    public function forceDelete(User $user, Payroll $payroll): bool
    {
        return true;
    }
}
