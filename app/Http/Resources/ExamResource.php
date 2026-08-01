<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExamResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'session_id' => $this->session_id,
            'exam_name' => $this->exam_name,
            'exam_date' => $this->exam_date,
            'exam_time' => $this->exam_time,
            'exam_hour' => $this->exam_hour,
            'exam_minute' => $this->exam_minute,
            'full_marks' => $this->full_marks,
            'passing_marks' => $this->passing_marks,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
