<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class DiaryEntry extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'date', 'content', 'mood'];

    protected $casts = [
        'date' => 'date',
    ];

    protected $appends = ['is_locked'];

    /**
     * An entry is locked once its date has passed (i.e. it is no longer today).
     */
    public function getIsLockedAttribute(): bool
    {
        return $this->date->toDateString() !== Carbon::today()->toDateString();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
