<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoTutorial extends Model
{
    protected $table = 'video_tutorial';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'title', 'vid_title', 'description', 'thumb_path', 'dir_path',
        'img_name', 'thumb_name', 'video_link', 'created_by',
    ];

    public function classSections()
    {
        return $this->belongsToMany(\App\Models\ClassSection::class,
        'video_tutorial_class_sections','video_tutorial_id', 'class_section_id')->withPivot('id','video_tutorial_id','class_section_id');
    }
}
