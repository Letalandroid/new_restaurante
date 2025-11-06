<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SettingsPayroll extends Model
{
    use HasFactory;

    protected $table = 'settings_payrolls';

    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Obtiene un valor de configuración por clave.
     */
    public static function getValue(string $key, $default = null)
    {
        return self::where('key', $key)->value('value') ?? $default;
    }

    /**
     * Actualiza o crea una configuración.
     */
    public static function setValue(string $key, $value): void
    {
        self::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
