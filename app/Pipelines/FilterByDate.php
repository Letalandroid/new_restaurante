<?php

namespace App\Pipelines;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class FilterByDate
{
    public function __construct(
        private ?string $fecha = null
    ) {}

    public function __invoke(Builder $builder, Closure $next)
    {
        // Filtrar por fecha exacta si está definida
        if (!is_null($this->fecha)) {
           $builder->whereDate('date', $this->fecha);
        }

        return $next($builder);
    }
}