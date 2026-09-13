<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'pengguna';
    }

    public function rules(): array
    {
        return [
            'facility_id' => 'required|exists:facilities,id',
            'tanggal'     => 'required|date|after_or_equal:today',
            'start_time'  => [
                'required',
                'date_format:H:i',
                'regex:/^(0[7-9]|1[0-9]):(00|30)$/' // 07.00 - 19.30 kelipatan 30 menit
            ],
            'end_time'    => [
                'required',
                'date_format:H:i',
                'after:start_time',
                'regex:/^(0[7-9]|1[0-9]|20):(00|30)$/' // hingga 20.00
            ],
            'tujuan'      => 'required|string|max:200',
        ];
    }

    public function messages(): array
    {
        return [
            'facility_id.required'   => 'Fasilitas kampus wajib dipilih.',
            'tanggal.required'       => 'Tanggal peminjaman wajib diisi.',
            'tanggal.after_or_equal' => 'Tanggal tidak boleh mendahului hari ini.',
            'start_time.required'    => 'Jam mulai peminjaman wajib diisi.',
            'start_time.regex'       => 'Jam mulai harus antara 07.00-19.30 dengan interval 30 menit.',
            'end_time.after'         => 'Jam selesai harus lebih besar dari jam mulai.',
            'tujuan.required'        => 'Tujuan penggunaan sarana wajib diisi.',
            'tujuan.max'             => 'Tujuan penggunaan maksimal 200 karakter.'
        ];
    }
}