<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $table = 'roles';
    protected $fillable = ['name', 'is_active', 'is_superadmin', 'is_staff', 'is_student', 'is_parent', 'is_admin'];

    public function permissionCategories(): BelongsToMany
    {
        return $this->belongsToMany(PermissionCategory::class, 'roles_permissions', 'role_id', 'permission_category_id')
            ->withPivot('is_active');
    }

    public function staff(): BelongsToMany
    {
        return $this->belongsToMany(Staff::class, 'staff_roles', 'role_id', 'staff_id')
            ->withPivot('is_active');
    }

    public function isActive(): bool
    {
        return $this->is_active === 1 || $this->is_active === '1';
    }
}
