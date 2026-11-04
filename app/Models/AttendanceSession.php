<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_id',
        'user_id',
        'computer_id',
        'check_in_at',
        'scheduled_end_at',
        'actual_end_at',
        'status',
    ];

    protected $casts = [
        'check_in_at' => 'datetime',
        'scheduled_end_at' => 'datetime',
        'actual_end_at' => 'datetime',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function computer()
    {
        return $this->belongsTo(Computer::class);
    }
}

