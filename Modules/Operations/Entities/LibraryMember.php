<?php

namespace Modules\Operations\Entities;

use Illuminate\Database\Eloquent\Model;

class LibraryMember extends Model
{
    protected $table = 'libarary_members';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [ 'library_card_no', 'member_type', 'member_id','is_active'];
}
