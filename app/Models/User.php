<?php

namespace App\Models;

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
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Find user by username for authentication
     * Note: Passwords are stored in plain text (legacy compatibility)
     */
    public function findForPassport(string $username): ?self
    {
        return $this->where('username', $username)->first();
    }

    /**
     * Validate plain text password
     * Note: Plain text comparison (no hashing for legacy compatibility)
     */
    public function validateForPassportPasswordGrant(string $password): bool
    {
        return $this->password === $password;
    }

    /**
     * Get the student record if user is a student
     */
    public function student(): HasOne
    {
        return $this->hasOne(Student::class, 'parent_id', 'id');
    }

    /**
     * Get the staff record if user is staff
     */
    public function staff(): HasOne
    {
        return $this->hasOne(Staff::class, 'user_id', 'id');
    }

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->is_active === 'yes' || $this->is_active === 1 || $this->is_active === true;
    }

    /**
     * Get the related entity (student/staff) based on role
     */
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