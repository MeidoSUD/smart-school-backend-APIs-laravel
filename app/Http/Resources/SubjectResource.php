<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'subject_name' => $this->subject_name,
            'subject_code' => $this->subject_code,
            'subject_type' => $this->subject_type,
            'is_active' => $this->is_active,
        ];
    }
}
