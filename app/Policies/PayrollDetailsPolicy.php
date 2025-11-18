<?php

namespace App\Policies;

use App\Models\PayrollDetail;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PayrollDetailsPolicy
{
    /**
     * Determine whether the user can view any payrolls.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('ver detalles nominas');
    }

    /**
     * Determine whether the user can view a specific payrolldetail.
     */
    public function view(User $user, PayrollDetail $payrolldetail): bool
    {
        return $user->can('ver detalles nominas');
    }

    /**
     * Determine whether the user can create payrolls.
     */
    public function create(User $user): bool
    {
        return $user->can('crear detalles nominas');
    }

    /**
     * Determine whether the user can update a payrolldetail.
     */
    public function update(User $user, PayrollDetail $payrolldetail): bool
    {
        return $user->can('editar detalles nominas');
    }

    /**
     * Determine whether the user can delete a payrolldetail.
     */
    public function delete(User $user, PayrollDetail $payrolldetail): bool
    {
        return $user->can('eliminar detalles nominas');
    }

    /**
     * Determine whether the user can restore a payrolldetail.
     */
    public function restore(User $user, PayrollDetail $payrolldetail): bool
    {
        return true;
    }

    /**
     * Determine whether the user can permanently delete a payrolldetail.
     */
    public function forceDelete(User $user, PayrollDetail $payrolldetail): bool
    {
        return true;
    }
}
