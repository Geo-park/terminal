<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'computer_id',
        'reservation_date',
        'start_time',
        'end_time',
        'start_at',
        'end_at',
        'purpose',
        'notes',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'reservation_date' => 'date',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function computer()
    {
        return $this->belongsTo(Computer::class);
    }

    public function accessCode()
    {
        return $this->hasOne(AccessCode::class);
    }

    public function attendanceSession()
    {
        return $this->hasOne(AttendanceSession::class);
    }
}

