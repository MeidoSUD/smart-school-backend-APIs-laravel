<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatConnection extends Model
{
    protected $table = 'chat_connections';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['chat_user_one', 'chat_user_two', 'ip', 'time'];
}
