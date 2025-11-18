<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralHoliday extends Model
{
    use HasFactory;

    protected $table = 'general_holidays';

    protected $fillable = [
        'name',
        'date',
        'is_recurring',
    ];

    /**
     * Verifica si el día indicado es feriado.
     */
    public static function isHoliday($date): bool
    {
        return self::where('date', $date)
            ->orWhere(function ($query) use ($date) {
                // Si el feriado es recurrente, compara solo el mes y el día
                $query->where('is_recurring', true)
                      ->whereRaw('DATE_FORMAT(date, "%m-%d") = ?', [date('m-d', strtotime($date))]);
            })
            ->exists();
    }
}
