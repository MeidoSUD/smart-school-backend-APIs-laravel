<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PermissionGroup extends Model
{
    protected $table = 'permission_group';
    protected $fillable = ['permission_group', 'is_active'];

    public function categories(): HasMany
    {
        return $this->hasMany(PermissionCategory::class, 'permission_group_id');
    }
}
