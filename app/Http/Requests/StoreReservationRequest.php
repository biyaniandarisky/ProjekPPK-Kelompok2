<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'pengguna';
    }

    public function rules(): array
    {
        return [
            'facility_id' => [
                'required',
                'integer',
                Rule::exists('facilities', 'id')->where('status', 'aktif'),
            ],
            'tanggal' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:today',
            ],
            'start_time' => [
                'required',
                'date_format:H:i',
                'regex:/^(0[7-9]|1[0-9]):(00|30)$/',
            ],
            'end_time' => [
                'required',
                'date_format:H:i',
                'after:start_time',
            ],
            'tujuan' => [
                'required',
                'string',
                'min:5',
                'max:500',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'facility_id.required'   => 'Fasilitas wajib dipilih.',
            'facility_id.exists'     => 'Fasilitas tidak ditemukan atau sedang tidak aktif.',
            'tanggal.required'       => 'Tanggal wajib diisi.',
            'tanggal.after_or_equal' => 'Tanggal tidak boleh di masa lalu.',
            'start_time.required'    => 'Jam mulai wajib diisi.',
            'start_time.regex'       => 'Jam mulai harus 07:00–19:30 dengan interval 30 menit.',
            'end_time.after'         => 'Jam selesai harus setelah jam mulai.',
            'tujuan.required'        => 'Tujuan penggunaan wajib diisi.',
            'tujuan.min'             => 'Tujuan penggunaan minimal 5 karakter.',
            'tujuan.max'             => 'Tujuan penggunaan maksimal 500 karakter.',
        ];
    }
}