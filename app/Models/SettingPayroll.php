<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SettingPayroll extends Model
{
    protected $table = 'settings_payrolls';
    protected $fillable = ['key', 'value'];
}
