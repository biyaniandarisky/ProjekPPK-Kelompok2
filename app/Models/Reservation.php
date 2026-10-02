<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'facility_id',
        'user_id',
        'petugas_id',
        'tanggal',
        'start_time',
        'end_time',
        'tujuan',
        'status',
        'alasan_batal',
        'alasan_tolak',
    ];

    protected $casts = [
        'tanggal'    => 'date:Y-m-d',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /* =========================================================
     |  RELASI
     ========================================================= */

    /**
     * Fasilitas yang direservasi.
     */
    public function facility()
    {
        return $this->belongsTo(Facility::class, 'facility_id');
    }

    /**
     * Pemesan.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Petugas yang memproses.
     */
    public function petugas()
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    /**
     * Notifikasi terkait reservasi ini.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'reservation_id');
    }

    /* =========================================================
     |  SCOPE
     ========================================================= */

    /**
     * Scope: reservasi berstatus pending.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: reservasi berstatus approved.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope: reservasi berstatus rejected.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope: reservasi berstatus cancelled.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope: reservasi aktif (pending + approved).
     */
    public function scopeAktif($query)
    {
        return $query->whereIn('status', ['pending', 'approved']);
    }

    /**
     * Scope: reservasi yang akan datang.
     */
    public function scopeAkanDatang($query)
    {
        return $query->where('tanggal', '>=', now()->toDateString())
            ->orderBy('tanggal')
            ->orderBy('start_time');
    }

    /**
     * Scope: reservasi hari ini.
     */
    public function scopeHariIni($query)
    {
        return $query->whereDate('tanggal', now()->toDateString());
    }

    /**
     * Scope: filter berdasarkan user.
     */
    public function scopeUntukUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: filter berdasarkan fasilitas.
     */
    public function scopeUntukFasilitas($query, $facilityId)
    {
        return $query->where('facility_id', $facilityId);
    }

    /**
     * Scope: filter berdasarkan status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /* =========================================================
     |  HELPER
     ========================================================= */

    /**
     * Cek apakah reservasi pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Cek apakah reservasi approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Cek apakah reservasi rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Cek apakah reservasi cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Cek apakah reservasi masih aktif (pending/approved).
     */
    public function isAktif(): bool
    {
        return in_array($this->status, ['pending', 'approved']);
    }

    /**
     * Cek apakah reservasi bisa dibatalkan.
     * Aturan: status pending/approved + minimal 2 jam sebelum start_time.
     */
    public function bisaDibatalkan(): bool
    {
        if (!$this->isAktif()) {
            return false;
        }

        $tglStr = $this->tanggal instanceof Carbon
            ? $this->tanggal->format('Y-m-d')
            : $this->tanggal;

        $startDateTime = Carbon::parse($tglStr . ' ' . $this->start_time);

        // Minimal 2 jam (120 menit) sebelum start_time
        return now()->diffInMinutes($startDateTime, false) >= 120;
    }

    /**
     * Cek apakah reservasi bisa di-approve.
     */
    public function bisaDiApprove(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Cek apakah reservasi bisa di-reject.
     */
    public function bisaDiReject(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Cek apakah reservasi bisa di-emergency cancel.
     */
    public function bisaEmergencyCancel(): bool
    {
        return $this->status === 'approved';
    }

    /* =========================================================
     |  ACCESSOR
     ========================================================= */

    /**
     * Label status yang ramah dibaca.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'   => 'Menunggu',
            'approved'  => 'Disetujui',
            'rejected'  => 'Ditolak',
            'cancelled' => 'Dibatalkan',
            'expired'   => 'Kedaluwarsa',
            default     => ucfirst($this->status),
        };
    }

    /**
     * Warna badge status (untuk view).
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending'   => 'bg-amber-50 text-amber-700',
            'approved'  => 'bg-emerald-50 text-emerald-700',
            'rejected'  => 'bg-rose-50 text-rose-700',
            'cancelled' => 'bg-slate-100 text-slate-600',
            'expired'   => 'bg-slate-100 text-slate-600',
            default     => 'bg-slate-100 text-slate-600',
        };
    }

    /**
     * Format waktu lengkap: "08:00 - 08:30 WIB".
     */
    public function getWaktuLengkapAttribute(): string
    {
        $start = substr($this->start_time, 0, 5);
        $end   = substr($this->end_time, 0, 5);
        return "{$start} - {$end} WIB";
    }

    /**
     * Format tanggal lengkap: "10 Oktober 2026".
     */
    public function getTanggalLengkapAttribute(): string
    {
        return $this->tanggal
            ? Carbon::parse($this->tanggal)->translatedFormat('d F Y')
            : '-';
    }

    /**
     * Durasi reservasi dalam menit.
     */
    public function getDurasiMenitAttribute(): int
    {
        $start = Carbon::parse($this->start_time);
        $end   = Carbon::parse($this->end_time);
        return $start->diffInMinutes($end);
    }

    /**
     * Durasi reservasi dalam format "1 jam 30 menit".
     */
    public function getDurasiLabelAttribute(): string
    {
        $menit = $this->durasi_menit;
        if ($menit < 60) {
            return $menit . ' menit';
        }
        $jam = floor($menit / 60);
        $sisa = $menit % 60;
        return $sisa > 0 ? "{$jam} jam {$sisa} menit" : "{$jam} jam";
    }
}