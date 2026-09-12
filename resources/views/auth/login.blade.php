@extends('layouts.app')

@section('content')
<div class="min-h-[75vh] flex items-center justify-center px-4 py-8">
    <div class="w-full max-w-md bg-white rounded-3xl shadow-xl border border-slate-200 p-8 space-y-6">
        <div class="text-center space-y-1">
            <div class="w-12 h-12 rounded-2xl bg-blue-900 text-white font-black text-2xl mx-auto flex items-center justify-center">K</div>
            <h2 class="text-2xl font-black text-slate-900">Login Satu Pintu</h2>
            <p class="text-xs text-slate-500">Akses untuk Mahasiswa, Dosen, Petugas, dan Admin</p>
        </div>

        <form action="{{ route('login') }}" method="POST" class="space-y-4 text-xs">
            @csrf
            <div>
                <label class="block font-bold text-slate-700 mb-1">Alamat Email</label>
                <input type="email" name="email" required value="{{ old('email') }}" placeholder="nama@kampus.ac.id" class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Kata Sandi (Password)</label>
                <input type="password" name="password" required placeholder="Minimal 6 karakter" class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none">
            </div>

            <button type="submit" class="w-full py-3 bg-blue-900 hover:bg-blue-800 text-white font-bold rounded-xl shadow transition">
                Masuk ke Sistem
            </button>

            <div class="text-center pt-2">
                <p class="text-xs text-slate-500">
                    Mahasiswa/Dosen baru? <a href="{{ route('register') }}" class="font-bold text-blue-900 hover:underline">Registrasi Mandiri di sini</a>.
                </p>
            </div>
        </form>
    </div>
</div>
@endsection