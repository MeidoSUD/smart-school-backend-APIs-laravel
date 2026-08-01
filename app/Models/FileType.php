<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FileType extends Model
{
    protected $table = 'filetypes';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['file_extension', 'file_mime', 'file_size', 'image_extension', 'image_mime', 'image_size', 'created_at'];
}
