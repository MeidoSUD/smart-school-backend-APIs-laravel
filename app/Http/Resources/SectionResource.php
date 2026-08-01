<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'section_name' => $this->section_name,
            'section_code' => $this->section_code,
            'is_active' => $this->is_active,
        ];
    }
}
