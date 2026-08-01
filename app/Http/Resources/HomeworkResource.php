<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HomeworkResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'staff_id' => $this->staff_id,
            'class_id' => $this->class_id,
            'section_id' => $this->section_id,
            'subject_id' => $this->subject_id,
            'homework_date' => $this->homework_date,
            'submit_date' => $this->submit_date,
            'marks' => $this->marks,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'subject' => $this->whenLoaded('subject', fn() => [
                'id' => $this->subject->id,
                'name' => $this->subject->subject_name,
            ]),
            'class' => $this->whenLoaded('class', fn() => [
                'id' => $this->class->id,
                'name' => $this->class->class,
            ]),
            'section' => $this->whenLoaded('section', fn() => [
                'id' => $this->section->id,
                'name' => $this->section->section_name,
            ]),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
