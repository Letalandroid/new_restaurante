<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    /**
     * Table name (optional if it matches plural of the model)
     */
    protected $table = 'attendances';

    /**
     * The attributes that are mass assignable.
     */
protected $fillable = [
    'employee_id',
    'work_date',
    'check_in',
    'check_out',
    'status_id',
    'justification',
];

    /**
     * Relationship: one attendance belongs to one user (employee)
     */
 public function status()
{
    return $this->belongsTo(AttendanceStatus::class, 'status_id');
}


   // Relación con Employee
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    /**
     * Accessor to get formatted date
     */
    public function getFormattedDateAttribute()
    {
        return \Carbon\Carbon::parse($this->work_date)->format('y/m/d');
    }

    /**
     * Accessor to calculate worked hours (if both times exist)
     */
public function getWorkedHoursAttribute()
{
    if ($this->check_in && $this->check_out) {
        // Combinar con una fecha base neutral sin zona horaria
        $baseDate = '2025-01-01';

        // Crear objetos DateTime sin zona horaria (neutral)
        $checkIn = new \DateTime("$baseDate {$this->check_in}");
        $checkOut = new \DateTime("$baseDate {$this->check_out}");

        // Si la salida es menor que la entrada, se asume día siguiente
        if ($checkOut < $checkIn) {
            $checkOut->modify('+1 day');
        }

        // Calcular la diferencia directamente
        $interval = $checkIn->diff($checkOut);

        // Obtener horas y minutos de la diferencia
        $hours = $interval->h + ($interval->d * 24); // incluye días si pasó medianoche
        $minutes = $interval->i;

        return sprintf('%02d:%02d', $hours, $minutes);
    }

    return null;
}




}
