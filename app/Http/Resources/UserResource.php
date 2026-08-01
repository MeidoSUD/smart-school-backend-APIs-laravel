<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_type' => $this->user_type,
            'role' => $this->role,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'address' => $this->address,
            'lang_id' => $this->lang_id,
            'student' => new StudentResource($this->whenLoaded('student')),
            'staff' => new StaffResource($this->whenLoaded('staff')),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
