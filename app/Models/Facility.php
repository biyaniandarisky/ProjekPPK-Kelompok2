<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_fasilitas',
        'tipe',
        'lokasi',
        'kapasitas',
        'deskripsi',
        'foto',
        'status',
    ];

    protected $casts = [
        'kapasitas'  => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /* =========================================================
     |  RELASI
     ========================================================= */

    /**
     * Reservasi untuk fasilitas ini.
     */
    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'facility_id');
    }

    /**
     * Laporan untuk fasilitas ini.
     */
    public function reports()
    {
        return $this->hasMany(Report::class, 'facility_id');
    }

    /**
     * Reservasi aktif (pending + approved).
     */
    public function activeReservations()
    {
        return $this->hasMany(Reservation::class, 'facility_id')
            ->whereIn('status', ['pending', 'approved']);
    }

    /**
     * Laporan terbuka (baru + diproses).
     */
    public function openReports()
    {
        return $this->hasMany(Report::class, 'facility_id')
            ->whereIn('status_laporan', ['baru', 'diproses']);
    }

    /* =========================================================
     |  SCOPE
     ========================================================= */

    /**
     * Scope: hanya fasilitas aktif.
     */
    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    /**
     * Scope: hanya fasilitas yang tidak nonaktif (tampil di pengunjung).
     */
    public function scopeTersedia($query)
    {
        return $query->where('status', '!=', 'nonaktif');
    }

    /**
     * Scope: hanya fasilitas dalam perbaikan.
     */
    public function scopeDalamPerbaikan($query)
    {
        return $query->where('status', 'dalam_perbaikan');
    }

    /**
     * Scope: filter berdasarkan tipe.
     */
    public function scopeTipe($query, $tipe)
    {
        return $query->where('tipe', $tipe);
    }

    /**
     * Scope: filter berdasarkan lokasi.
     */
    public function scopeLokasi($query, $lokasi)
    {
        return $query->where('lokasi', $lokasi);
    }

    /**
     * Scope: cari berdasarkan nama.
     */
    public function scopeCari($query, $keyword)
    {
        return $query->where('nama_fasilitas', 'like', '%' . $keyword . '%');
    }

    /* =========================================================
     |  HELPER
     ========================================================= */

    /**
     * Cek apakah fasilitas aktif.
     */
    public function isAktif(): bool
    {
        return $this->status === 'aktif';
    }

    /**
     * Cek apakah fasilitas dalam perbaikan.
     */
    public function isDalamPerbaikan(): bool
    {
        return $this->status === 'dalam_perbaikan';
    }

    /**
     * Cek apakah fasilitas nonaktif.
     */
    public function isNonaktif(): bool
    {
        return $this->status === 'nonaktif';
    }

    /**
     * Cek apakah fasilitas bisa direservasi.
     */
    public function bisaDireservasi(): bool
    {
        return $this->status === 'aktif';
    }

    /**
     * Cek apakah fasilitas punya laporan terbuka.
     */
    public function hasOpenReports(): bool
    {
        return $this->openReports()->exists();
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
            'aktif'            => 'Tersedia',
            'dalam_perbaikan'  => 'Dalam Perbaikan',
            'nonaktif'         => 'Nonaktif',
            default            => ucfirst($this->status),
        };
    }

    /**
     * Warna badge status (untuk view).
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'aktif'            => 'bg-emerald-50 text-emerald-700',
            'dalam_perbaikan'  => 'bg-amber-50 text-amber-700',
            'nonaktif'         => 'bg-slate-100 text-slate-600',
            default            => 'bg-slate-100 text-slate-600',
        };
    }

    /**
     * URL foto fasilitas.
     */
    public function getFotoUrlAttribute(): ?string
    {
        if (!$this->foto) {
            return null;
        }
        // Kalau foto sudah URL lengkap
        if (str_starts_with($this->foto, 'http')) {
            return $this->foto;
        }
        // Kalau path di storage
        return asset('storage/' . $this->foto);
    }
}