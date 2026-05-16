<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatUser extends Model
{
    protected $table = 'chat_users';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['user_type', 'staff_id', 'student_id', 'create_staff_id', 'create_student_id', 'is_active'];
}
