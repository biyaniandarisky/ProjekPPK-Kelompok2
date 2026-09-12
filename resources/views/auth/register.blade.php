@extends('layouts.app')

@section('content')
<div class="min-h-[75vh] flex items-center justify-center px-4 py-8">
    <div class="w-full max-w-md bg-white rounded-3xl shadow-xl border border-slate-200 p-8 space-y-6">
        <div class="text-center space-y-1">
            <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white font-black text-2xl mx-auto flex items-center justify-center">K</div>
            <h2 class="text-2xl font-black text-slate-900">Registrasi Pengguna</h2>
            <p class="text-xs text-slate-500">Pendaftaran akun Mahasiswa, Dosen, atau Staf</p>
        </div>

        <form action="{{ route('register') }}" method="POST" class="space-y-4 text-xs">
            @csrf
            <div>
                <label class="block font-bold text-slate-700 mb-1">Nama Lengkap</label>
                <input type="text" name="name" required value="{{ old('name') }}" placeholder="Contoh: Siti Aisyah" class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Email Institusi Kampus</label>
                <input type="email" name="email" required value="{{ old('email') }}" placeholder="nama@kampus.ac.id" class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Password (Min. 6 Karakter)</label>
                <input type="password" name="password" required placeholder="Password rahasia" class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none">
            </div>

            <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-[11px] text-amber-800">
                <strong>Catatan:</strong> Akun baru akan berstatus <em>pending</em> dan diverifikasi terlebih dahulu oleh admin kampus sebelum bisa login.
            </div>

            <button type="submit" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow transition">
                Daftar Sekarang
            </button>
        </form>
    </div>
</div>
@endsection