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

    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'facility_id');
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'facility_id');
    }
}