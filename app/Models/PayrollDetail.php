<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollDetail extends Model
{
    use HasFactory;

    protected $table = 'payroll_details';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'payroll_id',
        'concept',
        'amount',
        'type',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'type' => 'string',
    ];

    /**
     * Relationship with Payroll
     */
    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }
}
