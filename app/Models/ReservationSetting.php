<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservationSetting extends Model
{
    use HasFactory;

    protected $table = 'reservation_settings';

    protected $fillable = [
        'waiting_minutes',
        'auto_expire',
    ];

    protected $casts = [
        'waiting_minutes' => 'integer',
        'auto_expire' => 'boolean',
    ];
}