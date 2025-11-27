<?php

namespace App\Pipelines;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class FilterByNameHistorial
{
    public function handle($query, Closure $next)
    {
        $search = request('search');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('dish', function (Builder $dishQuery) use ($search) {
                    $dishQuery->where('name', 'ILIKE', "%{$search}%");
                })
                ->orWhereHas('product', function (Builder $prodQuery) use ($search) {
                    $prodQuery->where('name', 'ILIKE', "%{$search}%");
                });
            });
        }

        return $next($query);
    }
}