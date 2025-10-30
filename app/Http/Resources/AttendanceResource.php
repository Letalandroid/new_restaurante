<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class AttendanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'employee_name' => $this->employee ? $this->employee->name : null,
            'work_date' => Carbon::parse($this->work_date)->format('d-m-Y'),
            'check_in' => $this->check_in ? Carbon::parse($this->check_in)->format('H:i') : null,
            'check_out' => $this->check_out ? Carbon::parse($this->check_out)->format('H:i') : null,
            'status_id' => $this->status_id, 
            'status' => $this->status ? $this->status->name : null,
            'justification' => $this->justification,
            'worked_hours' => $this->worked_hours, // accesor del modelo
            'created_at' => Carbon::parse($this->created_at)->format('d-m-Y H:i:s A'),
            'updated_at' => Carbon::parse($this->updated_at)->format('d-m-Y H:i:s A'),
        ];
    }
}
