<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Explicit fillable list — never mass-assign sensitive fields.
     * 'password' is intentionally included because it is always hashed
     * before storage via the cast below.
     */
    protected $fillable = ['name', 'email', 'password'];

    /**
     * Fields excluded from all serialized output (toArray / toJson / API responses).
     * Prevents password hash and session token from ever appearing in responses.
     */
    protected $hidden = ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'];

    /**
     * Automatic type casts.
     * 'hashed' cast ensures bcrypt is applied automatically on assignment —
     * the raw password string is never stored.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',   // Laravel 10+ automatic bcrypt
        'created_at'        => 'datetime',
        'updated_at'        => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function diaryEntries()
    {
        return $this->hasMany(DiaryEntry::class);
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    public function alerts()
    {
        return $this->hasMany(Alert::class);
    }
}
