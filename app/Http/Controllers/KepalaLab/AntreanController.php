<?php

namespace App\Http\Controllers\KepalaLab;

use App\Http\Controllers\Controller;
use App\Models\Computer;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AntreanController extends Controller
{
    /**
     * Tampilkan Halaman Antrean Persetujuan Reservasi Khusus Kepala Lab.
     */
    public function index(Request $request)
    {
        $today = Carbon::today('Asia/Jakarta');

        $computerFilter = $request->query('computer_id');
        $search = $request->query('search');
        $tab = $request->query('tab', 'pending'); // 'pending' or 'history'

        // 1. Query Antrean Pending
        $pendingQuery = Reservation::with(['user', 'computer'])
            ->where('status', 'PENDING_APPROVAL')
            ->orderBy('reservation_date')
            ->orderBy('start_time');

        if ($computerFilter) {
            $pendingQuery->where('computer_id', $computerFilter);
        }

        if ($search) {
            $pendingQuery->where(function ($q) use ($search) {
                $q->whereHas('user', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%")
                        ->orWhere('nim', 'like', "%{$search}%");
                })->orWhere('purpose', 'like', "%{$search}%");
            });
        }

        $pendingReservations = $pendingQuery->get();

        // 2. Query Riwayat Keputusan Terbaru (Approved, Rejected, Checked In, Completed)
        $historyQuery = Reservation::with(['user', 'computer', 'accessCode', 'approver'])
            ->whereIn('status', ['APPROVED', 'REJECTED', 'CHECKED_IN', 'COMPLETED'])
            ->latest('updated_at');

        if ($computerFilter) {
            $historyQuery->where('computer_id', $computerFilter);
        }

        if ($search) {
            $historyQuery->where(function ($q) use ($search) {
                $q->whereHas('user', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%")
                        ->orWhere('nim', 'like', "%{$search}%");
                })->orWhere('purpose', 'like', "%{$search}%");
            });
        }

        $historyReservations = $historyQuery->take(30)->get();

        // 3. Statistik Metrik & Data Filter
        $computers = Computer::where('is_active', true)->orderBy('computer_code')->get();
        $pendingCount = Reservation::where('status', 'PENDING_APPROVAL')->count();
        $approvedTodayCount = Reservation::whereDate('updated_at', $today)
            ->whereIn('status', ['APPROVED', 'CHECKED_IN', 'COMPLETED'])
            ->count();
        $rejectedTodayCount = Reservation::whereDate('updated_at', $today)
            ->where('status', 'REJECTED')
            ->count();

        return view('kepala-lab.antrean', compact(
            'pendingReservations',
            'historyReservations',
            'computers',
            'pendingCount',
            'approvedTodayCount',
            'rejectedTodayCount',
            'computerFilter',
            'search',
            'tab'
        ));
    }
}

