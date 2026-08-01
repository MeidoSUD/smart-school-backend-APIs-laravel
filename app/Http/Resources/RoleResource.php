<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'is_active' => $this->is_active,
            'is_superadmin' => $this->is_superadmin,
            'permissions' => PermissionCategoryResource::collection($this->whenLoaded('permissionCategories')),
        ];
    }
}
