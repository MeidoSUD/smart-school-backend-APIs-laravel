<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SidebarSubMenu extends Model
{
    protected $table = 'sidebar_sub_menus';
    protected $primaryKey = 'id';
    public $timestamps = true;
    const UPDATED_AT = 'updated_at';
    const CREATED_AT = 'created_at';

    protected $fillable = [
        'sidebar_menu_id', 'permission_group_id', 'menu_name',
        'menu_url', 'is_active', 'sort_order',
    ];

    public function menu()
    {
        return $this->belongsTo(SidebarMenu::class, 'sidebar_menu_id');
    }
}
