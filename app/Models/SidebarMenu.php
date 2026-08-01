<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SidebarMenu extends Model
{
    protected $table = 'sidebar_menus';
    protected $primaryKey = 'id';
    public $timestamps = true;
    const UPDATED_AT = 'updated_at';
    const CREATED_AT = 'created_at';

    protected $fillable = [
        'permission_group_id', 'menu_id', 'menu_name', 'icon',
        'menu_url', 'module_name', 'is_active', 'sort_order',
    ];

    public function permissionGroup()
    {
        return $this->belongsTo(PermissionGroup::class, 'permission_group_id');
    }

    public function subMenus()
    {
        return $this->hasMany(SidebarSubMenu::class, 'sidebar_menu_id');
    }
}
