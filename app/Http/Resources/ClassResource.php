<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'class' => $this->class,
            'section' => $this->section,
            'section_id' => $this->section_id,
            'is_active' => $this->is_active,
        ];
    }
}
