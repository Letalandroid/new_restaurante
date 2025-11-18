<?php

namespace App\Pipelines;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class FilterByCodeRyName
{
    public function __construct(private ?string $search) {}
    
    public function __invoke(Builder $builder, Closure $next)
    {
        if (!$this->search) {
            return $next($builder);
        }

        $normalized = strtolower(trim(preg_replace('/\s+/', ' ', $this->search)));
        $terms = explode(' ', $normalized);
        
        $builder->where(function ($q) use ($terms) {
            foreach ($terms as $term) {
                $q->orWhere(function ($sub) use ($term) {
                    // Buscar por código de reservación
                    $sub->where('reservation_code', 'ILIKE', "%{$term}%")
                        // Buscar por nombre del cliente (relación customer)
                        ->orWhereHas('customer', function ($customerQuery) use ($term) {
                            $customerQuery->where('name', 'ILIKE', "%{$term}%")
                                         ->orWhere('lastname', 'ILIKE', "%{$term}%");
                        })
                        // Buscar por estado (activo/inactivo)
                        ->orWhereRaw("CASE WHEN state THEN 'activo' ELSE 'inactivo' END ILIKE ?", ["%{$term}%"]);
                });
            }
        });

        return $next($builder);
    }
}