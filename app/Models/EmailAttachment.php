<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailAttachment extends Model
{
    protected $table = 'email_attachments';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'email_id', 'file_name', 'file_path', 'file_size', 'file_type',
    ];
}
