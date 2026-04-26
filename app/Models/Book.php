<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Book extends Model
{
    protected $table = 'books';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'book_title', 'book_no', 'isbn_no', 'subject', 'rack_no', 'publish',
        'author', 'qty', 'perunitcost', 'postdate', 'description', 'available', 'is_active',
    ];
}

class BookIssue extends Model
{
    protected $table = 'book_issues';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['book_id', 'member_id', 'duereturn_date', 'return_date', 'issue_date', 'is_returned', 'is_active'];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class, 'book_id');
    }
}

class LibraryMember extends Model
{
    protected $table = 'library_members';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['member_id', 'member_type', 'library_card_id', 'issue_limit', 'join_date', 'end_date', 'is_active'];
}