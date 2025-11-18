<?php

namespace App\Pipelines;

use Closure;

class FilterByEmployeeDate
{
    protected $date;
    protected $dateFrom;
    protected $dateTo;

    public function __construct($date = null, $dateFrom = null, $dateTo = null)
    {
        $this->date = $date;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }

    public function handle($query, Closure $next)
    {
        if ($this->date) {
            // Filtrar por fecha exacta
            $query->whereDate('work_date', $this->date);
        } elseif ($this->dateFrom && $this->dateTo) {
            // Filtrar por rango de fechas
            $query->whereBetween('work_date', [$this->dateFrom, $this->dateTo]);
        } elseif ($this->dateFrom) {
            // Solo fecha desde
            $query->whereDate('work_date', '>=', $this->dateFrom);
        } elseif ($this->dateTo) {
            // Solo fecha hasta
            $query->whereDate('work_date', '<=', $this->dateTo);
        }

        return $next($query);
    }
}