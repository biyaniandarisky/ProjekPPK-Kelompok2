@extends('layouts.app')

@section('title', $tab === 'register' ? 'Registrasi Akun — Reservasi Kampus' : 'Login — Reservasi Kampus')

@section('content')
@php
    $inputBase = 'w-full h-11 px-3 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-900/20 focus:border-blue-900';
@endphp

<div class="flex items-start justify-center px-4 py-10 sm:py-14"
     x-data="{
        tab: '{{ $tab }}',
        lupa: false,
        pending: {{ session('pending_notice') ? 'true' : 'false' }},
        switchTab(t) {
            this.tab = t;
            history.replaceState(null, '', t === 'login' ? '{{ route('login', [], false) }}' : '{{ route('register', [], false) }}');
            document.title = t === 'login' ? 'Login — Reservasi Kampus' : 'Registrasi Akun — Reservasi Kampus';
        }
     }">

    <div class="w-full max-w-md bg-white rounded-3xl shadow-xl border border-slate-200 px-6 sm:px-8 py-8">

        {{-- Header --}}
        <div class="text-center">
            <h2 class="mt-3 text-2xl font-extrabold text-slate-900" x-text="tab === 'login' ? 'Login' : 'Registrasi Akun'">{{ $tab === 'register' ? 'Registrasi Akun' : 'Login' }}</h2>
            <p class="mt-1 text-[11px] text-slate-500"
               x-text="tab === 'login' ? 'Sistem Reservasi & Pelaporan Fasilitas Kampus Terpadu' : 'Isi data diri Anda dengan benar'">
                {{ $tab === 'register' ? 'Isi data diri Anda dengan benar' : 'Sistem Reservasi & Pelaporan Fasilitas Kampus Terpadu' }}
            </p>
        </div>

        {{-- Tab --}}
        <div class="mt-6 grid grid-cols-2 border-b-2 border-slate-100 text-sm font-bold" role="tablist">
            <button type="button" role="tab" @click="switchTab('login')"
                    :aria-selected="tab === 'login'"
                    class="pb-2.5 -mb-0.5 border-b-2 transition"
                    :class="tab === 'login' ? 'border-[#1e3a8a] text-[#1e3a8a]' : 'border-transparent text-slate-400 hover:text-slate-600'">
                Login
            </button>
            <button type="button" role="tab" @click="switchTab('register')"
                    :aria-selected="tab === 'register'"
                    class="pb-2.5 -mb-0.5 border-b-2 transition"
                    :class="tab === 'register' ? 'border-[#1e3a8a] text-[#1e3a8a]' : 'border-transparent text-slate-400 hover:text-slate-600'">
                Registrasi Mandiri
            </button>
        </div>

        {{-- ============ PANEL LOGIN ============ --}}
        <form x-show="tab === 'login'" @if($tab !== 'login') x-cloak @endif
              action="{{ route('login') }}" method="POST" class="mt-6 space-y-4">
            @csrf

            <div>
                <label for="login-email" class="block text-xs font-bold text-slate-800 mb-1.5">Alamat Email Kampus</label>
                <div class="relative">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="M3 7l9 6 9-6"/></svg>
                    <input id="login-email" type="email" name="email" required autocomplete="email"
                           value="{{ $tab === 'login' ? old('email') : '' }}" placeholder="nama@kampus.ac.id"
                           class="{{ $inputBase }} pl-9">
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="login-password" class="block text-xs font-bold text-slate-800">Kata Sandi (Password)</label>
                    <button type="button" @click="lupa = !lupa" class="text-[11px] font-bold text-blue-700 hover:underline">Lupa Password?</button>
                </div>
                <div class="relative">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="5" y="11" width="14" height="9" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="M8 11V8a4 4 0 118 0v3"/></svg>
                    <input id="login-password" type="password" name="password" required autocomplete="current-password"
                           placeholder="Minimal 6 karakter" class="{{ $inputBase }} pl-9">
                </div>
                <p x-show="lupa" x-cloak class="mt-2 text-[11px] text-slate-500 bg-slate-50 border border-slate-200 rounded-lg px-3 py-2">
                    Untuk mereset password, silakan hubungi admin atau petugas kampus.
                </p>
            </div>

            <button type="submit"
                    class="w-full h-12 bg-[#1e3a8a] hover:bg-[#172f70] text-white text-sm font-bold rounded-xl shadow-md transition">
                Login ke Sistem
            </button>

            <p class="text-center text-[11px] text-slate-500 pt-1 leading-relaxed">
                Belum punya akun?
                <button type="button" @click="switchTab('register')" class="font-extrabold text-[#1e3a8a] hover:underline">Daftar di sini</button>.
                Akses untuk Mahasiswa, Dosen, Staf, dan Petugas Kampus.
            </p>
        </form>

        {{-- ============ PANEL REGISTRASI ============ --}}
        <form x-show="tab === 'register'" @if($tab !== 'register') x-cloak @endif
              action="{{ route('register') }}" method="POST" enctype="multipart/form-data" class="mt-6 space-y-3.5">
            @csrf

            <div>
                <label for="reg-name" class="block text-xs font-bold text-slate-700 mb-1.5">Nama Lengkap <span class="text-rose-500">*</span></label>
                <input id="reg-name" type="text" name="name" required maxlength="100" autocomplete="name"
                       value="{{ old('name') }}" placeholder="Nama lengkap" class="{{ $inputBase }}">
                @error('name')<p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="reg-nim" class="block text-xs font-bold text-slate-700 mb-1.5">NIM / NIP <span class="text-rose-500">*</span></label>
                <input id="reg-nim" type="text" name="nim_nip" required maxlength="30" inputmode="numeric"
                       value="{{ old('nim_nip') }}" placeholder="Nomor identitas" class="{{ $inputBase }}">
                @error('nim_nip')<p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="reg-email" class="block text-xs font-bold text-slate-700 mb-1.5">Email <span class="text-rose-500">*</span></label>
                <input id="reg-email" type="email" name="email" required autocomplete="email"
                       value="{{ $tab === 'register' ? old('email') : '' }}" placeholder="email@kampus.ac.id" class="{{ $inputBase }}">
                @error('email')<p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="reg-hp" class="block text-xs font-bold text-slate-700 mb-1.5">No. HP</label>
                <input id="reg-hp" type="tel" name="no_hp" autocomplete="tel" inputmode="tel"
                       value="{{ old('no_hp') }}" placeholder="08xxxxxxxxxx" class="{{ $inputBase }}">
                @error('no_hp')<p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="reg-pass" class="block text-xs font-bold text-slate-700 mb-1.5">Password <span class="text-rose-500">*</span></label>
                <input id="reg-pass" type="password" name="password" required minlength="8" autocomplete="new-password"
                       placeholder="min. 8 karakter" class="{{ $inputBase }}">
                @error('password')<p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="reg-pass2" class="block text-xs font-bold text-slate-700 mb-1.5">Konfirmasi Password <span class="text-rose-500">*</span></label>
                <input id="reg-pass2" type="password" name="password_confirmation" required minlength="8" autocomplete="new-password"
                       placeholder="ulangi password" class="{{ $inputBase }}">
            </div>

            <div x-data="{ drag: false, fileName: '' }">
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Upload KTM / KTP</label>
                <label @dragover.prevent="drag = true" @dragleave.prevent="drag = false"
                       @drop.prevent="drag = false; $refs.file.files = $event.dataTransfer.files; fileName = $refs.file.files[0] ? $refs.file.files[0].name : ''"
                       class="flex items-center justify-center gap-2 h-20 px-3 text-center rounded-lg border border-dashed cursor-pointer text-[12px] transition"
                       :class="drag ? 'border-blue-600 bg-blue-50 text-blue-700' : 'border-slate-300 bg-slate-50 text-slate-500 hover:bg-slate-100'">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12.8l-8.6 8.6a5.5 5.5 0 01-7.8-7.8l9-9a3.7 3.7 0 015.2 5.2l-9 9a1.8 1.8 0 01-2.6-2.6l8.4-8.4"/></svg>
                    <span x-show="!fileName">Klik untuk upload atau drag &amp; drop</span>
                    <span x-show="fileName" x-cloak class="font-semibold text-slate-700 break-all" x-text="fileName"></span>
                    <input type="file" name="ktm" x-ref="file" class="hidden" accept=".jpg,.jpeg,.png,.pdf"
                           @change="fileName = $event.target.files[0] ? $event.target.files[0].name : ''">
                </label>
                <p class="mt-1 text-[10px] text-slate-400">Format JPG, PNG, atau PDF. Maksimal 2 MB. Digunakan admin untuk memverifikasi identitas Anda.</p>
                @error('ktm')<p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p>@enderror
            </div>

            <button type="submit"
                    class="w-full h-11 bg-[#0f2540] hover:bg-[#0b1c31] text-white text-sm font-bold rounded-lg shadow-md transition">
                Daftar
            </button>

            <p class="text-center text-[11px] text-slate-500">
                Sudah punya akun?
                <button type="button" @click="switchTab('login')" class="font-extrabold text-blue-700 hover:underline">Masuk</button>
            </p>
        </form>
    </div>

    {{-- ============ POPUP: AKUN SEDANG DIVERIFIKASI ============ --}}
    <div x-show="pending" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="pending = false"></div>
        <div class="relative w-full max-w-sm bg-white rounded-2xl shadow-2xl p-6 text-center space-y-3">
            <div class="mx-auto w-14 h-14 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2"/></svg>
            </div>
            <h3 class="text-base font-extrabold text-slate-900">Akun Anda Sedang Diverifikasi Admin</h3>
            <p class="text-xs text-slate-500 leading-relaxed">
                Terima kasih telah mendaftar. Data Anda sedang diperiksa oleh admin kampus.
                Anda <strong class="text-slate-700">belum dapat login</strong> sampai akun disetujui. Silakan coba login kembali nanti.
            </p>
            <button type="button" @click="pending = false"
                    class="w-full h-10 bg-[#0f2540] hover:bg-[#0b1c31] text-white text-xs font-bold rounded-lg transition">Mengerti</button>
        </div>
    </div>
</div>
@endsection