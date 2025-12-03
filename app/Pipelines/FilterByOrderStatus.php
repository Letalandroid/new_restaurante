<?php

namespace App\Pipelines;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Orders;

class FilterByOrderStatus
{
    public function __construct(private $orderStatus) {}

    public function __invoke(Builder $builder, Closure $next)
    {
        if (!is_null($this->orderStatus) && $this->orderStatus !== '') {

            if ($this->orderStatus === 'ocupado') {
                // Mesas con órdenes activas
                $builder->whereExists(function ($query) {
                    $query->select()
                        ->from('orders')
                        ->whereColumn('orders.idTable', 'tables.id')
                        ->where('orders.state', '!=', 'finalizado');
                });

            } elseif ($this->orderStatus === 'disponible') {
                // Mesas SIN órdenes activas
                $builder->whereNotExists(function ($query) {
                    $query->select()
                        ->from('orders')
                        ->whereColumn('orders.idTable', 'tables.id')
                        ->where('orders.state', '!=', 'finalizado');
                });
            }
        }

        return $next($builder);
    }
}
