<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'title', 'notice_date', 'publish_date', 'message', 'visible_at',
        'class_id', 'section_id', 'created_by', 'created_for', 'is_active', 'attachment',
    ];
}

class NotificationStatus extends Model
{
    protected $table = 'notification_status';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['notification_id', 'user_id', 'visible_date_read'];
}

class OfflinePayment extends Model
{
    protected $table = 'offline_payments';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['student_session_id', 'amount', 'payment_date', 'payment_mode', 'status', 'description'];
}

class OnlineExam extends Model
{
    protected $table = 'online_exams';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'exam_title', 'exam_type', 'class_id', 'section_id', 'subject_id',
        'duration', 'minimum_percentage', 'max_attempts', 'is_active',
    ];
}

class OnlineExamQuestion extends Model
{
    protected $table = 'online_exam_questions';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['online_exam_id', 'question_id', 'optional'];
}

class OnlineExamResult extends Model
{
    protected $table = 'online_exam_results';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['online_exam_id', 'student_id', 'answers', 'obtained_marks', 'attended_on', 'is_active'];
}