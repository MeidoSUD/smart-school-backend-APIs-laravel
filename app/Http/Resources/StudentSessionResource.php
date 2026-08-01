<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentSessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'session_id' => $this->session_id,
            'student_id' => $this->student_id,
            'class_id' => $this->class_id,
            'section_id' => $this->section_id,
            'student' => new StudentResource($this->whenLoaded('student')),
            'class' => $this->whenLoaded('class', fn() => [
                'id' => $this->class->id,
                'name' => $this->class->class,
            ]),
            'section' => $this->whenLoaded('section', fn() => [
                'id' => $this->section->id,
                'name' => $this->section->section_name,
            ]),
            'session' => $this->whenLoaded('session', fn() => [
                'id' => $this->session->id,
                'name' => $this->session->session,
            ]),
        ];
    }
}
