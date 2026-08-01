<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PermissionCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'is_active' => $this->is_active,
            'group' => $this->whenLoaded('group', fn() => [
                'id' => $this->group->id,
                'name' => $this->group->permission_group,
            ]),
        ];
    }
}
