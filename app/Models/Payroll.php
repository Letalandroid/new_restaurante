<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    protected $table = 'payrolls';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'employee_id',
        'start_date',
        'end_date',
        'base_salary',
        'laborable_days',
        'days_present',
        'days_absent',
        'days_justified',
        'hours_worked',
        'overtime_hours',
        'overtime_payment',
        'bonuses',
        'absence_discount',
        'proportional_base',
        'gross_total',
        'afp_discount',
        'essalud_contribution',
        'net_total',
        'paid',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'base_salary' => 'decimal:2',
        'hours_worked' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'overtime_payment' => 'decimal:2',
        'bonuses' => 'decimal:2',
        'absence_discount' => 'decimal:2',
        'proportional_base' => 'decimal:2',
        'gross_total' => 'decimal:2',
        'afp_discount' => 'decimal:2',
        'essalud_contribution' => 'decimal:2',
        'net_total' => 'decimal:2',
        'paid' => 'boolean',
    ];

    /**
     * Relationship with Employee
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Relationship with PayrollDetail
     */
    public function details()
    {
        return $this->hasMany(PayrollDetail::class);
    }

    /**
     * Accessor for total ingresos (suma de detalles tipo ingreso)
     */
    public function getTotalIngresosAttribute()
    {
        return $this->details()
            ->where('type', 'ingreso')
            ->sum('amount');
    }

    /**
     * Accessor for total descuentos (suma de detalles tipo descuento)
     */
    public function getTotalDescuentosAttribute()
    {
        return $this->details()
            ->where('type', 'descuento')
            ->sum('amount');
    }
}
