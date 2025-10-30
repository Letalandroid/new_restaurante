<?php

namespace App\Pipelines;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class FilterByStateEmployeeAttendance
{
    public function __construct(private ?string $state) {}

    public function __invoke(Builder $builder, Closure $next)
    {
        // Si no se seleccionó ningún estado, no se filtra
        if (!$this->state) {
            return $next($builder);
        }

        // Filtrar usando la relación status
        $builder->whereHas('status', function ($query) {
            // Compatibilidad MySQL/PostgreSQL, búsqueda insensible a mayúsculas
            $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($this->state) . '%']);
        });

        return $next($builder);
    }
}
