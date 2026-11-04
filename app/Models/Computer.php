<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Computer extends Model
{
    use HasFactory;

    protected $fillable = [
        'computer_code',
        'computer_name',
        'specs',
        'location',
        'status',
        'is_active',
        'maintenance_started_at',
        'maintenance_until',
        'maintenance_reason',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'maintenance_started_at' => 'datetime',
        'maintenance_until' => 'datetime',
    ];

    /**
     * Cek apakah unit komputer sedang aktif dalam masa pemeliharaan (maintenance).
     */
    public function isInMaintenance(): bool
    {
        if ($this->status !== 'MAINTENANCE') {
            return false;
        }

        if ($this->maintenance_until && Carbon::now('Asia/Jakarta')->gte($this->maintenance_until)) {
            return false;
        }

        return true;
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function attendanceSessions()
    {
        return $this->hasMany(AttendanceSession::class);
    }
}

