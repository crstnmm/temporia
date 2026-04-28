<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'alert_date', 'title', 'alert_time', 'priority', 'description'];

    protected $casts = [
        'alert_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
