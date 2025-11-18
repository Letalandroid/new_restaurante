<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceStatus extends Model
{
    use HasFactory;

    /**
     * Table name (optional if it matches plural of the model)
     */
    protected $table = 'attendance_statuses';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'description',
        'active',
    ];

    /**
     * Relationship: one status can have many attendances
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'status_id');
    }

    /**
     * Scope: only active statuses
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
