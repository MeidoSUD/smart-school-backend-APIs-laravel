<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id';
    public $timestamps = true;
    const UPDATED_AT = 'updated_at';
    const CREATED_AT = 'created_at';

    protected $fillable = [
        'user_type',
        'role',
        'email',
        'password',
        'address',
        'phone_number',
        'lang_id',
        'student_id',
        'parent_id',
        'staff_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [];

    public function student(): HasOne
    {
        return $this->hasOne(\App\Models\Student::class, 'user_id', 'id');
    }

    public function staff(): HasOne
    {
        return $this->hasOne(\App\Models\Staff::class, 'user_id', 'id');
    }

    public function parent(): HasOne
    {
        return $this->hasOne(\App\Models\Student::class, 'parent_id', 'id');
    }

    public function roleModel(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role', 'name');
    }

    public function isActive(): bool
    {
        return $this->is_active === 'yes' || $this->is_active === 1;
    }

    public function getRelatedUser()
    {
        return match ($this->role) {
            'student' => $this->student,
            'parent' => $this->parent,
            'teacher', 'staff', 'accountant', 'librarian' => $this->staff,
            default => null,
        };
    }

    public function isSuperAdmin(): bool
    {
        $role = $this->roleModel;
        return $role && ($role->is_superadmin == 1);
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles);
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $role = $this->roleModel;

        if (!$role) {
            return false;
        }

        return $role->permissionCategories()
            ->where('permission_category.name', $permission)
            ->where('roles_permissions.is_active', 1)
            ->exists();
    }

    public function permissions(): array
    {
        if ($this->isSuperAdmin()) {
            return ['*'];
        }

        $role = $this->roleModel;

        if (!$role) {
            return [];
        }

        return $role->permissionCategories()
            ->where('roles_permissions.is_active', 1)
            ->pluck('permission_category.name')
            ->toArray();
    }
}
