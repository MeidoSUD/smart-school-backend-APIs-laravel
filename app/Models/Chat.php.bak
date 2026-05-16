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

class ChatConnection extends Model
{
    protected $table = 'chat_connections';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['chat_user_one', 'chat_user_two', 'ip', 'time'];
}

class ChatMessage extends Model
{
    protected $table = 'chat_messages';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['message', 'chat_user_id', 'ip', 'time', 'is_first', 'is_read', 'chat_connection_id', 'created_at'];
}