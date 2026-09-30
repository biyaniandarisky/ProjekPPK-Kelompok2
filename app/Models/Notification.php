<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Relasi opsional ke model Reservation (jika menggunakan eager loading)
     */
    public function reservation()
    {
        return $this->belongsTo(Reservation::class, 'reservation_id');
    }

    public function report()
    {
        return $this->belongsTo(Report::class, 'report_id');
    }
}