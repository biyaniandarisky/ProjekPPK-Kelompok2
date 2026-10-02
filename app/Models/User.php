<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'nim_nip',
        'no_hp',
        'ktm_path',
        'password',
        'role',
        'status_verifikasi',
        'tipe_pengguna', // kalau ada kolom ini
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'ktm_path',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'created_at'        => 'datetime',
            'updated_at'        => 'datetime',
        ];
    }

    /* =========================================================
     |  RELASI
     ========================================================= */

    /**
     * Reservasi yang dibuat user.
     */
    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'user_id');
    }

    /**
     * Laporan yang dibuat user.
     */
    public function reports()
    {
        return $this->hasMany(Report::class, 'user_id');
    }

    /**
     * Reservasi yang diproses petugas ini.
     */
    public function processedReservations()
    {
        return $this->hasMany(Reservation::class, 'petugas_id');
    }

    /**
     * Laporan yang ditangani petugas ini.
     */
    public function handledReports()
    {
        return $this->hasMany(Report::class, 'petugas_id');
    }

    /**
     * Notifikasi milik user.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    /**
     * Notifikasi belum dibaca.
     */
    public function unreadNotifications()
    {
        return $this->hasMany(Notification::class, 'user_id')
            ->where('is_read', false);
    }

    /* =========================================================
     |  SCOPE
     ========================================================= */

    /**
     * Scope: user dengan role admin.
     */
    public function scopeAdmin($query)
    {
        return $query->where('role', 'admin');
    }

    /**
     * Scope: user dengan role petugas.
     */
    public function scopePetugas($query)
    {
        return $query->where('role', 'petugas');
    }

    /**
     * Scope: user dengan role pengguna.
     */
    public function scopePengguna($query)
    {
        return $query->where('role', 'pengguna');
    }

    /**
     * Scope: user dengan status verifikasi tertentu.
     */
    public function scopeStatusVerifikasi($query, $status)
    {
        return $query->where('status_verifikasi', $status);
    }

    /**
     * Scope: user yang sudah diverifikasi.
     */
    public function scopeVerified($query)
    {
        return $query->where('status_verifikasi', 'verified');
    }

    /**
     * Scope: user yang menunggu verifikasi.
     */
    public function scopePending($query)
    {
        return $query->where('status_verifikasi', 'pending');
    }

    /* =========================================================
     |  HELPER ROLE
     ========================================================= */

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isPetugas(): bool
    {
        return $this->role === 'petugas';
    }

    public function isPengguna(): bool
    {
        return $this->role === 'pengguna';
    }

    /* =========================================================
     |  HELPER VERIFIKASI
     ========================================================= */

    public function isVerified(): bool
    {
        return $this->status_verifikasi === 'verified';
    }

    public function isPending(): bool
    {
        return $this->status_verifikasi === 'pending';
    }

    public function isRejected(): bool
    {
        return $this->status_verifikasi === 'rejected';
    }

    /**
     * Cek apakah user bisa login.
     */
    public function bisaLogin(): bool
    {
        // Admin & petugas selalu bisa login
        if (in_array($this->role, ['admin', 'petugas'])) {
            return true;
        }
        // Pengguna harus verified
        return $this->isVerified();
    }

    /* =========================================================
     |  HELPER NOTIFIKASI
     ========================================================= */

    /**
     * Hitung notifikasi belum dibaca.
     */
    public function unreadNotificationsCount(): int
    {
        return $this->unreadNotifications()->count();
    }

    /**
     * Tandai semua notifikasi sebagai sudah dibaca.
     */
    public function markAllNotificationsAsRead(): int
    {
        return $this->unreadNotifications()->update(['is_read' => true]);
    }

    /* =========================================================
     |  ACCESSOR
     ========================================================= */

    /**
     * Inisial nama (untuk avatar).
     */
    public function getInisialAttribute(): string
    {
        $name = trim($this->name);
        if (empty($name)) {
            return '?';
        }
        $parts = explode(' ', $name);
        if (count($parts) === 1) {
            return strtoupper(substr($parts[0], 0, 1));
        }
        return strtoupper(substr($parts[0], 0, 1) . substr($parts[count($parts) - 1], 0, 1));
    }

    /**
     * Label role yang ramah dibaca.
     */
    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'admin'    => 'Administrator',
            'petugas'  => 'Petugas',
            'pengguna' => 'Pengguna',
            default    => ucfirst($this->role),
        };
    }

    /**
     * Label status verifikasi.
     */
    public function getStatusVerifikasiLabelAttribute(): string
    {
        return match ($this->status_verifikasi) {
            'verified' => 'Terverifikasi',
            'pending'  => 'Menunggu Verifikasi',
            'rejected' => 'Ditolak',
            default    => ucfirst($this->status_verifikasi ?? '-'),
        };
    }

    /**
     * Warna badge status verifikasi.
     */
    public function getStatusVerifikasiColorAttribute(): string
    {
        return match ($this->status_verifikasi) {
            'verified' => 'bg-emerald-50 text-emerald-700',
            'pending'  => 'bg-amber-50 text-amber-700',
            'rejected' => 'bg-rose-50 text-rose-700',
            default    => 'bg-slate-100 text-slate-600',
        };
    }

    /**
     * URL KTM (kalau ada).
     */
    public function getKtmUrlAttribute(): ?string
    {
        if (!$this->ktm_path) {
            return null;
        }
        return route('admin.users.ktm', $this->id);
    }
}