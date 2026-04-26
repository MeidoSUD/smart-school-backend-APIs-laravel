<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffRating extends Model
{
    protected $table = 'staff_rating';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['staff_id', 'comment', 'rate', 'user_id', 'role', 'status'];
}

class StudentTimeline extends Model
{
    protected $table = 'student_timeline';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'student_id', 'title', 'timeline_date', 'description', 'document',
        'status', 'date', 'created_by', 'visible', 'slug',
    ];
}

class VideoTutorial extends Model
{
    protected $table = 'video_tutorial';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'title', 'vid_title', 'description', 'thumb_path', 'dir_path',
        'img_name', 'thumb_name', 'video_link', 'created_by',
    ];
}

class Visitor extends Model
{
    protected $table = 'visitors_book';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'staff_id', 'student_session_id', 'source', 'purpose', 'name',
        'email', 'contact', 'id_proof', 'no_of_people', 'date', 'in_time',
        'out_time', 'note', 'image', 'meeting_with',
    ];
}

class ApplyLeave extends Model
{
    protected $table = 'apply_leave';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'student_session_id', 'apply_date', 'from_date', 'to_date',
        'reason', 'status', 'approve_by', 'docs',
    ];
}

class Category extends Model
{
    protected $table = 'categories';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['category', 'is_active'];
}

class BloodGroup extends Model
{
    protected $table = 'blood_group';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['name', 'is_active'];
}

class House extends Model
{
    protected $table = 'school_houses';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['house_name', 'is_active'];
}

class CustomField extends Model
{
    protected $table = 'custom_fields';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'name', 'belong_to', 'type', 'bs_column', 'validation',
        'field_values', 'show_table', 'visible_on_table', 'weight', 'is_active',
    ];
}

class CustomFieldValue extends Model
{
    protected $table = 'custom_field_values';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['belong_table_id', 'custom_field_id', 'field_value'];
}

class FileType extends Model
{
    protected $table = 'filetypes';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['file_extension', 'file_mime', 'file_size', 'image_extension', 'image_mime', 'image_size'];
}