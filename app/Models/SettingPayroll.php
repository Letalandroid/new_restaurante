<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class SettingPayroll extends Model
{
    protected $table = 'settings_payrolls';
    protected $fillable = ['key', 'value'];
}
