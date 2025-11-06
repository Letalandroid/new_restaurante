<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'payment_type',          // 'fijo' o 'por_hora'
        'base_salary',           // sueldo base (si aplica)
        'hourly_rate',           // costo por hora (si aplica)
        'has_punctuality_bonus', // booleano
        'punctuality_bonus',     // monto del bono (si aplica)
        'shift_hours',           // horas de jornada
        'rate_overtime',         // multiplicador de horas extra
        'state',                 // activo o inactivo
    ];

    protected $casts = [
        'state' => 'boolean',
        'has_punctuality_bonus' => 'boolean',
        'base_salary' => 'float',
        'hourly_rate' => 'float',
        'punctuality_bonus' => 'float',
        'shift_hours' => 'float',
        'rate_overtime' => 'float',
    ];

    /**
     * Relación: Un tipo de empleado puede tener muchos empleados.
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'employee_type_id');
    }

    /**
     * Verifica si tiene empleados relacionados.
     */
    public function tieneRelaciones(): bool
    {
        return $this->employees()->exists();
    }

    /**
     * Devuelve el monto base para el cálculo del sueldo.
     */
    public function getBaseAmountAttribute(): float
    {
        return $this->payment_type === 'fijo'
            ? (float) $this->base_salary
            : (float) $this->hourly_rate;
    }

    /**
     * Determina si este tipo de empleado tiene derecho a bonificación de puntualidad.
     */
    public function getHasBonusAttribute(): bool
    {
        return $this->has_punctuality_bonus && $this->punctuality_bonus > 0;
    }
}
