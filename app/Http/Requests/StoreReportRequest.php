<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReportRequest extends FormRequest
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
                Rule::exists('facilities', 'id'),
            ],
            'kategori_laporan' => [
                'required',
                'string',
                Rule::in(['Kerusakan', 'Kebersihan', 'Fasilitas', 'Lainnya']),
            ],
            'deskripsi' => [
                'required',
                'string',
                'min:10',
                'max:1000',
            ],
            'foto' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'facility_id.required'      => 'Fasilitas wajib dipilih.',
            'facility_id.exists'        => 'Fasilitas tidak ditemukan.',
            'kategori_laporan.required' => 'Kategori kendala wajib dipilih.',
            'kategori_laporan.in'       => 'Kategori kendala tidak valid.',
            'deskripsi.required'        => 'Deskripsi wajib diisi.',
            'deskripsi.min'             => 'Deskripsi minimal 10 karakter.',
            'deskripsi.max'             => 'Deskripsi maksimal 1000 karakter.',
            'foto.image'                => 'File harus berupa gambar.',
            'foto.mimes'                => 'Format foto harus JPG, JPEG, PNG, atau WEBP.',
            'foto.max'                  => 'Ukuran foto maksimal 2 MB.',
        ];
    }
}