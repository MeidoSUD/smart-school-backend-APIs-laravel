<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PermissionCategory extends Model
{
    protected $table = 'permission_category';
    protected $fillable = ['permission_group_id', 'name', 'is_active'];

    public function group(): BelongsTo
    {
        return $this->belongsTo(PermissionGroup::class, 'permission_group_id');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'roles_permissions', 'permission_category_id', 'role_id')
            ->withPivot('is_active');
    }
}
