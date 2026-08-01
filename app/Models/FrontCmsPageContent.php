<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FrontCmsPageContent extends Model
{
    protected $table = 'front_cms_page_contents';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'page_id', 'title', 'slug', 'description', 'path', 'type',
        'is_active', 'sort_order',
    ];
}
