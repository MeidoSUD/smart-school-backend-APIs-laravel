<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    protected $table = 'chat_messages';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['message', 'chat_user_id', 'ip', 'time', 'is_first', 'is_read', 'chat_connection_id', 'created_at'];
}
