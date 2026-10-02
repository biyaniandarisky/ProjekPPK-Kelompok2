<?php

namespace App\Services;

use App\Exceptions\InvalidSlotException;
use App\Models\Notification;
use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReportService
{
    private const DESKRIPSI_MIN = 10;
    private const MAX_SPAM_PER_HARI = 5;
    private const DUPLIKAT_JAM = 24;

    public function create(array $data, int $userId): Report
    {
        if (strlen(trim($data['deskripsi'])) < self::DESKRIPSI_MIN) {
            throw new InvalidSlotException('Deskripsi minimal ' . self::DESKRIPSI_MIN . ' karakter.');
        }

        $hariIni = Report::where('user_id', $userId)
            ->whereDate('created_at', now()->toDateString())
            ->count();

        if ($hariIni >= self::MAX_SPAM_PER_HARI) {
            throw new InvalidSlotException('Anda sudah membuat ' . self::MAX_SPAM_PER_HARI . ' laporan hari ini.');
        }

        $duplikat = Report::where('user_id', $userId)
            ->where('facility_id', $data['facility_id'])
            ->where('created_at', '>=', now()->subHours(self::DUPLIKAT_JAM))
            ->whereIn('status_laporan', ['baru', 'diproses'])
            ->exists();

        if ($duplikat) {
            throw new InvalidSlotException('Anda sudah melaporkan fasilitas ini dalam ' . self::DUPLIKAT_JAM . ' jam terakhir.');
        }

        $laporan = DB::transaction(function () use ($data, $userId) {
            return Report::create([
                'facility_id'      => $data['facility_id'],
                'user_id'          => $userId,
                'kategori_laporan' => $data['kategori_laporan'],
                'deskripsi'        => $data['deskripsi'],
                'foto'             => $data['foto'] ?? null,
                'status_laporan'   => 'baru',
            ]);
        });

        $this->notifyPetugas($laporan);

        return $laporan;
    }

    private function notifyPetugas(Report $laporan): void
    {
        $petugas = User::where('role', 'petugas')->get();
        foreach ($petugas as $p) {
            Notification::create([
                'user_id'   => $p->id,
                'report_id' => $laporan->id,
                'judul'     => 'Laporan Baru',
                'pesan'     => 'Laporan baru dari ' . ($laporan->user->name ?? 'pengguna')
                                . ' di ' . ($laporan->facility->nama_fasilitas ?? 'fasilitas') . '.',
                'tipe'      => 'warning',
                'link'      => route('petugas.laporan.index'),
            ]);
        }
    }
}