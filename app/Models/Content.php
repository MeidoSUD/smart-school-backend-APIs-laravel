<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    protected $table = 'contents';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'title', 'type', 'is_public', 'class_id', 'cls_sec_id',
        'file', 'date', 'note', 'is_active', 'created_by',
    ];
}

class ShareContent extends Model
{
    protected $table = 'share_content';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'content_id', 'visible_to', 'created_by', 'share_date', 'valid_upto',
        'class_id', 'section_id', 'role_id',
    ];
}

class ClassSection extends Model
{
    protected $table = 'class_sections';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['class_id', 'section_id', 'is_active'];
}