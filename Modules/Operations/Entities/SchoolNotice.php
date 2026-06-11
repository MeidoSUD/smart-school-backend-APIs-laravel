<?php

namespace Modules\Operations\Entities;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Entities\User;

class SchoolNotice extends Model
{
    protected $table = 'send_notification';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'title',
        'publish_date',
        'date',
        'attachment',
        'message',
        'visible_student',
        'visible_staff',
        'visible_parent',
        'created_by',
        'created_id',
        'is_active',
    ];

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('is_active', 'yes')
            ->where('publish_date', '<=', now()->toDateString())
            ->orderByDesc('publish_date');
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return match ($user->role) {
            'student' => $query->where('visible_student', 'yes'),
            'parent' => $query->where('visible_parent', 'yes'),
            default => $query->whereRaw('1 = 0'),
        };
    }
}
