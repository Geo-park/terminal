<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReservationStreamController extends Controller
{
    /**
     * Stream status reservasi via Server-Sent Events (SSE).
     * Koneksi persisten — server push hanya saat status berubah.
     * Tidak ada polling terus-menerus dari browser, tidak membebani server.
     */
    public function stream(Request $request, Reservation $reservation): StreamedResponse
    {
        // Pastikan hanya pemilik reservasi yang dapat memantau
        abort_unless($reservation->user_id === auth()->id(), 403);

        // Jika status sudah bukan PENDING_APPROVAL, langsung kirim 1 event
        if ($reservation->status !== 'PENDING_APPROVAL') {
            return $this->immediateResponse($reservation);
        }

        return new StreamedResponse(function () use ($reservation) {
            // Tutup dan simpan session agar tidak memblokir request lain dari user yang sama
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_write_close();
            }

            // Bersihkan output buffer
            while (ob_get_level() > 0) {
                @ob_end_clean();
            }

            $lastStatus = $reservation->status;
            $lastUpdatedAt = $reservation->updated_at ? $reservation->updated_at->timestamp : 0;
            $timeout = 50; // Timeout 50 detik agar di bawah timeout server/proxy (60 detik)
            $startTime = time();
            $checkInterval = 1; // interval pengecekan ringan di server (1 detik)

            // Handshake awal SSE
            echo ": connected\n\n";
            @ob_flush();
            @flush();

            while (true) {
                // Hentikan jika browser telah memutuskan koneksi
                if (connection_aborted()) {
                    break;
                }

                // Hentikan jika waktu hidup koneksi SSE sudah 50 detik (browser akan reconnect otomatis)
                if ((time() - $startTime) >= $timeout) {
                    echo "event: timeout\n";
                    echo "data: {\"reconnect\":true}\n\n";
                    @ob_flush();
                    @flush();
                    break;
                }

                // Query status langsung dari database
                $fresh = Reservation::select('status', 'rejection_reason', 'approved_by', 'approved_at', 'updated_at')
                    ->find($reservation->id);

                if (!$fresh) {
                    break;
                }

                $currentUpdatedAt = $fresh->updated_at ? $fresh->updated_at->timestamp : 0;

                // Push ke client HANYA jika status berubah atau record di-update
                if ($fresh->status !== $lastStatus || $currentUpdatedAt !== $lastUpdatedAt) {
                    $lastStatus = $fresh->status;
                    $lastUpdatedAt = $currentUpdatedAt;

                    $approver = null;
                    if ($fresh->approved_by) {
                        $approver = User::select('name')->find($fresh->approved_by)?->name;
                    }

                    $payload = json_encode([
                        'status'           => $fresh->status,
                        'rejection_reason' => $fresh->rejection_reason,
                        'approver'         => $approver,
                        'approved_at'      => $fresh->approved_at ? $fresh->approved_at->toIso8601String() : null,
                    ]);

                    echo "event: status_changed\n";
                    echo "data: {$payload}\n\n";
                    @ob_flush();
                    @flush();

                    // Jika status sudah tidak pending (APPROVED, REJECTED, CANCELLED), tutup stream
                    if ($fresh->status !== 'PENDING_APPROVAL') {
                        break;
                    }
                }

                // Heartbeat setiap 20 detik agar koneksi tetap hidup
                if ((time() - $startTime) > 0 && (time() - $startTime) % 20 === 0) {
                    echo ": heartbeat\n\n";
                    @ob_flush();
                    @flush();
                }

                sleep($checkInterval);
            }
        }, 200, [
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache, no-store, must-revalidate',
            'X-Accel-Buffering' => 'no', // Disable buffer di Nginx / reverse proxy
            'Connection'        => 'keep-alive',
        ]);
    }

    /**
     * Respon kilat jika status saat connect sudah bukan pending
     */
    private function immediateResponse(Reservation $reservation): StreamedResponse
    {
        return new StreamedResponse(function () use ($reservation) {
            $approver = $reservation->approved_by
                ? User::select('name')->find($reservation->approved_by)?->name
                : null;

            $payload = json_encode([
                'status'           => $reservation->status,
                'rejection_reason' => $reservation->rejection_reason,
                'approver'         => $approver,
                'approved_at'      => $reservation->approved_at ? $reservation->approved_at->toIso8601String() : null,
            ]);

            echo "event: status_changed\n";
            echo "data: {$payload}\n\n";
            @ob_flush();
            @flush();
        }, 200, [
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache, no-store, must-revalidate',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
