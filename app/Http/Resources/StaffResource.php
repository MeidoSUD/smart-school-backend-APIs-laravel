<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaffResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'staff_name' => $this->staff_name,
            'fathers_name' => $this->fathers_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'gender' => $this->gender,
            'qualification' => $this->qualification,
            'date_of_birth' => $this->date_of_birth,
            'date_of_joining' => $this->date_of_joining,
            'employee_salary' => $this->employee_salary,
            'is_active' => $this->is_active,
            'designation' => $this->whenLoaded('designation', fn() => [
                'id' => $this->designation->id,
                'name' => $this->designation->staff_designation_name,
            ]),
            'department' => $this->whenLoaded('department', fn() => [
                'id' => $this->department->id,
                'name' => $this->department->department_name,
            ]),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
