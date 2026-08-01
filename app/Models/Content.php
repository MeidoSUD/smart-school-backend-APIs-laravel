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
        'created_at', 'updated_at',
    ];
}

class ShareContent extends Model
{
    protected $table = 'share_contents';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'send_to', 'title', 'share_date', 'valid_upto', 'description', 'created_by',
    ];
}

class ClassSectionContent extends Model
{
    protected $table = 'class_sections';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['class_id', 'section_id', 'is_active'];
}
