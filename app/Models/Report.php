<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'facility_id',
        'user_id',
        'petugas_id',
        'kategori_laporan',
        'deskripsi',
        'foto',
        'status_laporan',
        'catatan_resolusi',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /* =========================================================
     |  RELASI
     ========================================================= */

    /**
     * Fasilitas yang dilaporkan.
     */
    public function facility()
    {
        return $this->belongsTo(Facility::class, 'facility_id');
    }

    /**
     * Pelapor (pengguna).
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Petugas yang menangani.
     */
    public function petugas()
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    /* =========================================================
     |  SCOPE
     ========================================================= */

    /**
     * Scope: laporan berstatus baru.
     */
    public function scopeBaru($query)
    {
        return $query->where('status_laporan', 'baru');
    }

    /**
     * Scope: laporan berstatus diproses.
     */
    public function scopeDiproses($query)
    {
        return $query->where('status_laporan', 'diproses');
    }

    /**
     * Scope: laporan berstatus selesai.
     */
    public function scopeSelesai($query)
    {
        return $query->where('status_laporan', 'selesai');
    }

    /**
     * Scope: laporan berstatus ditolak.
     */
    public function scopeDitolak($query)
    {
        return $query->where('status_laporan', 'ditolak');
    }

    /**
     * Scope: laporan terbuka (baru + diproses).
     */
    public function scopeTerbuka($query)
    {
        return $query->whereIn('status_laporan', ['baru', 'diproses']);
    }

    /**
     * Scope: filter berdasarkan fasilitas.
     */
    public function scopeUntukFasilitas($query, $facilityId)
    {
        return $query->where('facility_id', $facilityId);
    }

    /**
     * Scope: filter berdasarkan pelapor.
     */
    public function scopeUntukPelapor($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: filter berdasarkan kategori.
     */
    public function scopeKategori($query, $kategori)
    {
        return $query->where('kategori_laporan', $kategori);
    }

    /* =========================================================
     |  HELPER
     ========================================================= */

    /**
     * Cek apakah laporan baru.
     */
    public function isBaru(): bool
    {
        return $this->status_laporan === 'baru';
    }

    /**
     * Cek apakah laporan sedang diproses.
     */
    public function isDiproses(): bool
    {
        return $this->status_laporan === 'diproses';
    }

    /**
     * Cek apakah laporan selesai.
     */
    public function isSelesai(): bool
    {
        return $this->status_laporan === 'selesai';
    }

    /**
     * Cek apakah laporan ditolak.
     */
    public function isDitolak(): bool
    {
        return $this->status_laporan === 'ditolak';
    }

    /**
     * Cek apakah laporan masih terbuka.
     */
    public function isTerbuka(): bool
    {
        return in_array($this->status_laporan, ['baru', 'diproses']);
    }

    /**
     * Cek apakah laporan bisa diproses petugas.
     */
    public function bisaDiproses(): bool
    {
        return $this->status_laporan === 'baru';
    }

    /**
     * Cek apakah laporan bisa diselesaikan.
     */
    public function bisaDiselesaikan(): bool
    {
        return $this->status_laporan === 'diproses';
    }

    /* =========================================================
     |  ACCESSOR
     ========================================================= */

    /**
     * Label status yang ramah dibaca.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status_laporan) {
            'baru'     => 'Baru',
            'diproses' => 'Diproses',
            'selesai'  => 'Selesai',
            'ditolak'  => 'Ditolak',
            default    => ucfirst($this->status_laporan),
        };
    }

    /**
     * Warna badge status (untuk view).
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status_laporan) {
            'baru'     => 'bg-blue-50 text-blue-700',
            'diproses' => 'bg-amber-50 text-amber-700',
            'selesai'  => 'bg-emerald-50 text-emerald-700',
            'ditolak'  => 'bg-rose-50 text-rose-700',
            default    => 'bg-slate-100 text-slate-600',
        };
    }

    /**
     * Label kategori yang ramah dibaca.
     */
    public function getKategoriLabelAttribute(): string
    {
        return $this->kategori_laporan ?? '-';
    }

    /**
     * URL foto laporan.
     */
    public function getFotoUrlAttribute(): ?string
    {
        if (!$this->foto) {
            return null;
        }
        if (str_starts_with($this->foto, 'http')) {
            return $this->foto;
        }
        return asset('storage/' . $this->foto);
    }
}