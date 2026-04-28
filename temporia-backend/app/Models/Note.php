<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'note_date', 'title', 'body', 'color'];

    protected $casts = [
        'note_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
