<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'pengguna';
    }

    public function rules(): array
    {
        return [
            'facility_id'      => 'required|exists:facilities,id',
            'kategori_laporan' => 'required|in:Kerusakan,Kebersihan,Fasilitas,Lainnya',
            'deskripsi'        => 'required|string|max:150',
            'foto'             => 'nullable|image|max:2048', // 2MB max
        ];
    }

    public function messages(): array
    {
        return [
            'facility_id.required'      => 'Fasilitas kampus wajib dipilih.',
            'kategori_laporan.required' => 'Kategori kendala wajib dipilih.',
            'deskripsi.required'        => 'Deskripsi masalah wajib diisi.',
            'deskripsi.max'             => 'Deskripsi maksimal 150 karakter.',
        ];
    }
}