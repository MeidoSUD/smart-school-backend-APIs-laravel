<?php

namespace Modules\Core\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';

    protected $primaryKey = 'id';

    public $timestamps = true;

    const UPDATED_AT = 'updated_at';
    const CREATED_AT = 'created_at';

    protected $fillable = [
        'user_id',
        'username',
        'password',
        'childs',
        'role',
        'lang_id',
        'currency_id',
        'verification_code',
        'is_active',
        'token',
    ];

    protected $hidden = [
        'password',
    ];

    public function student(): HasOne
    {
        return $this->hasOne(\Modules\Academic\Entities\Student::class, 'parent_id', 'id');
    }

    public function staff(): HasOne
    {
        return $this->hasOne(\Modules\Staff\Entities\Staff::class, 'user_id', 'id');
    }

    public function isActive(): bool
    {
        return $this->is_active === 'yes';
    }

    public function getRelatedUser()
    {
        return match ($this->role) {
            'student' => $this->student,
            'parent' => $this->student,
            'teacher', 'staff', 'accountant', 'librarian' => $this->staff,
            default => null,
        };
    }
}
