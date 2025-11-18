<?php

namespace App\Pipelines;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class FilterByEmployeeName
{
    protected ?string $search;

    public function __construct(?string $search = null)
    {
        $this->search = $search;
    }

    public function __invoke(Builder $builder, Closure $next)
    {
        if (!$this->search) {
            return $next($builder);
        }

        $normalized = strtolower(trim(preg_replace('/\s+/', ' ', $this->search)));
        $terms = explode(' ', $normalized);

        $builder->whereHas('employee', function ($query) use ($terms) {
            foreach ($terms as $term) {
                $query->whereRaw("LOWER(name) LIKE ?", ["%{$term}%"])
                      ->orWhereRaw("LOWER(codigo) LIKE ?", ["%{$term}%"]);
                // Si agregas apellidos en tu tabla employees, puedes incluir:
                // ->orWhereRaw("LOWER(apellidos) LIKE ?", ["%{$term}%"]);
            }
        });

        return $next($builder);
    }
}
