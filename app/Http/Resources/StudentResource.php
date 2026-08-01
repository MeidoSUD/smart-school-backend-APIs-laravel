<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'admission_no' => $this->admission_no,
            'admission_date' => $this->admission_date,
            'roll_no' => $this->roll_no,
            'first_name' => $this->First_name,
            'last_name' => $this->Last_name,
            'full_name' => $this->First_name . ' ' . $this->Last_name,
            'father_name' => $this->Father_name,
            'father_phone' => $this->Father_phone,
            'mother_name' => $this->Mother_name,
            'student_email' => $this->student_email,
            'student_phone' => $this->student_phone,
            'student_gender' => $this->student_gender,
            'dob' => $this->dob,
            'blood_group' => $this->blood_group,
            'religion' => $this->religion,
            'student_address' => $this->student_address,
            'student_photo' => $this->student_photo,
            'is_active' => $this->is_active,
            'student_status' => $this->student_status,
            'category' => new \App\Http\Resources\CategoryResource($this->whenLoaded('category')),
            'school_house' => $this->whenLoaded('schoolHouse', fn() => [
                'id' => $this->schoolHouse->id,
                'name' => $this->schoolHouse->house_name,
            ]),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
