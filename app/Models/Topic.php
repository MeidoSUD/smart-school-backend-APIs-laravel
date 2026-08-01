<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    protected $table = 'topic';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'subject_syllabus_id', 'topic_title', 'description',
        'date_from', 'date_to', 'completed_date', 'status', 'is_active',
    ];
}
