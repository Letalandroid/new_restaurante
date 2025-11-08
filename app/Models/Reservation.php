<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'customer_id',
        'number_people',
        'date',
        'hour',
        'waiting_hour', //Nuevo campo
        'reservation_code',
        'state',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'state' => 'boolean',
        'date' => 'date',
        'hour' => 'string',
        'waiting_hour' => 'string', //Nuevo cast
        'number_people' => 'integer',
    ];

    /**
     * Get the customer that owns the reservation.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Generate a unique reservation code.
     */
}