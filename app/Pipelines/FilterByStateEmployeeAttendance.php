<?php

namespace App\Pipelines;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class FilterByStateEmployeeAttendance
{
    public function __construct(private ?int $state) {}

    public function __invoke(Builder $builder, Closure $next)
    {
        // Si no se seleccionó ningún estado, no aplicar filtro
        if (!$this->state) {
            return $next($builder);
        }

        // Filtrar directamente por el ID del estado
        $builder->where('status_id', $this->state);

        return $next($builder);
    }
}
