<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reservation_id',
        'report_id',
        'judul',
        'pesan',
        'tipe',
        'link',
        'is_read',
    ];

    protected $casts = [
        'is_read'    => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /* =========================================================
     |  RELASI
     ========================================================= */

    /**
     * Pemilik notifikasi.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Reservasi terkait (opsional).
     */
    public function reservation()
    {
        return $this->belongsTo(Reservation::class, 'reservation_id');
    }

    /**
     * Laporan terkait (opsional).
     */
    public function report()
    {
        return $this->belongsTo(Report::class, 'report_id');
    }

    /* =========================================================
     |  SCOPE
     ========================================================= */

    /**
     * Scope: notifikasi belum dibaca.
     */
    public function scopeBelumDibaca($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope: notifikasi sudah dibaca.
     */
    public function scopeSudahDibaca($query)
    {
        return $query->where('is_read', true);
    }

    /**
     * Scope: filter berdasarkan user.
     */
    public function scopeUntukUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: notifikasi dengan tipe tertentu.
     */
    public function scopeTipe($query, $tipe)
    {
        return $query->where('tipe', $tipe);
    }

    /* =========================================================
     |  HELPER
     ========================================================= */

    /**
     * Tandai notifikasi sebagai sudah dibaca.
     */
    public function markAsRead(): bool
    {
        return $this->update(['is_read' => true]);
    }

    /**
     * Tandai semua notifikasi user sebagai sudah dibaca.
     */
    public static function markAllAsRead(int $userId): int
    {
        return static::where('user_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }

    /**
     * Hitung notifikasi belum dibaca untuk user.
     */
    public static function unreadCount(int $userId): int
    {
        return static::where('user_id', $userId)
            ->where('is_read', false)
            ->count();
    }

    /* =========================================================
     |  ACCESSOR
     ========================================================= */

    /**
     * Warna badge notifikasi berdasarkan tipe.
     */
    public function getWarnaAttribute(): string
    {
        return match ($this->tipe) {
            'success' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'danger'  => 'bg-rose-50 text-rose-700 border-rose-200',
            'warning' => 'bg-amber-50 text-amber-700 border-amber-200',
            'info'    => 'bg-blue-50 text-blue-700 border-blue-200',
            default   => 'bg-slate-100 text-slate-600 border-slate-200',
        };
    }

    /**
     * Waktu relatif (contoh: "2 jam lalu").
     */
    public function getWaktuRelatifAttribute(): string
    {
        return $this->created_at?->diffForHumans() ?? '-';
    }
}