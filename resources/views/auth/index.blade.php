@extends('layouts.app')

@section('title', ($tab ?? 'login') === 'register' ? 'Registrasi Akun' : 'Login')

@section('content')
@php
    $inputBase = 'w-full h-11 px-3 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-900';
    $tab = $tab ?? 'login';
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

    <div class="w-full max-w-md bg-white rounded-2xl shadow-lg border border-slate-200 px-6 sm:px-8 py-8">

        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-slate-900"
                x-text="tab === 'login' ? 'Login' : 'Registrasi Akun'">
                {{ $tab === 'register' ? 'Registrasi Akun' : 'Login' }}
            </h1>
            <p class="mt-1 text-xs text-slate-500"
               x-text="tab === 'login' ? 'Sistem Reservasi & Pelaporan Fasilitas Kampus' : 'Isi data diri Anda dengan benar'">
            </p>
        </div>

        {{-- LOGIN --}}
        <form x-show="tab === 'login'" @if($tab !== 'login') x-cloak @endif
              action="{{ route('login') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label for="login-email" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Alamat Email <span class="text-rose-500">*</span>
                </label>
                <input id="login-email" type="email" name="email" required
                       value="{{ old('email') }}"
                       placeholder="nama@kampus.ac.id"
                       class="{{ $inputBase }}">
                @error('email')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="login-password" class="block text-xs font-semibold text-slate-700">
                        Password <span class="text-rose-500">*</span>
                    </label>
                    <button type="button" @click="lupa = !lupa"
                            class="text-xs font-semibold text-blue-900 hover:underline">
                        Lupa Password?
                    </button>
                </div>
                <input id="login-password" type="password" name="password" required
                       placeholder="Minimal 8 karakter"
                       class="{{ $inputBase }}">
                <p x-show="lupa" x-cloak
                   class="mt-2 text-xs text-slate-500 bg-slate-50 border border-slate-200 rounded-lg px-3 py-2">
                    Untuk mereset password, silakan hubungi admin atau petugas kampus.
                </p>
            </div>

            <button type="submit"
                    class="w-full h-11 bg-blue-900 hover:bg-blue-800 text-white text-sm font-bold rounded-lg shadow-sm transition">
                Login ke Sistem
            </button>

            <p class="text-center text-xs text-slate-500 pt-2">
                Belum punya akun?
                <button type="button" @click="switchTab('register')"
                        class="font-bold text-blue-900 hover:underline">
                    Daftar di sini
                </button>
            </p>
        </form>

        {{-- REGISTER --}}
        <form x-show="tab === 'register'" @if($tab !== 'register') x-cloak @endif
              action="{{ route('register') }}" method="POST" enctype="multipart/form-data" class="space-y-3.5">
            @csrf

            <div>
                <label for="reg-name" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Nama Lengkap <span class="text-rose-500">*</span>
                </label>
                <input id="reg-name" type="text" name="name" required maxlength="100"
                       value="{{ old('name') }}" placeholder="Nama lengkap" class="{{ $inputBase }}">
                @error('name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="reg-nim" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    NIM / NIP <span class="text-rose-500">*</span>
                </label>
                <input id="reg-nim" type="text" name="nim_nip" required maxlength="30"
                       value="{{ old('nim_nip') }}" placeholder="Nomor identitas" class="{{ $inputBase }}">
                @error('nim_nip')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="reg-email" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Email <span class="text-rose-500">*</span>
                </label>
                <input id="reg-email" type="email" name="email" required
                       value="{{ old('email') }}" placeholder="email@kampus.ac.id" class="{{ $inputBase }}">
                @error('email')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="reg-hp" class="block text-xs font-semibold text-slate-700 mb-1.5">No. HP</label>
                <input id="reg-hp" type="tel" name="no_hp"
                       value="{{ old('no_hp') }}" placeholder="08xxxxxxxxxx" class="{{ $inputBase }}">
                @error('no_hp')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="reg-pass" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Password <span class="text-rose-500">*</span>
                </label>
                <input id="reg-pass" type="password" name="password" required minlength="8"
                       placeholder="Minimal 8 karakter" class="{{ $inputBase }}">
                @error('password')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="reg-pass2" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Konfirmasi Password <span class="text-rose-500">*</span>
                </label>
                <input id="reg-pass2" type="password" name="password_confirmation" required minlength="8"
                       placeholder="Ulangi password" class="{{ $inputBase }}">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Upload KTM / KTP <span class="text-slate-400 font-normal">(Opsional)</span>
                </label>
                <input type="file" name="ktm" accept=".jpg,.jpeg,.png,.pdf"
                       class="w-full text-sm file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-blue-900 file:text-white hover:file:bg-blue-800">
                <p class="mt-1 text-xs text-slate-400">Format JPG, PNG, atau PDF. Maks 2 MB.</p>
                @error('ktm')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <button type="submit"
                    class="w-full h-11 bg-blue-900 hover:bg-blue-800 text-white text-sm font-bold rounded-lg shadow-sm transition">
                Daftar
            </button>

            <p class="text-center text-xs text-slate-500">
                Sudah punya akun?
                <button type="button" @click="switchTab('login')"
                        class="font-bold text-blue-900 hover:underline">Masuk</button>
            </p>
        </form>
    </div>

    {{-- POPUP --}}
    <div x-show="pending" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="pending = false"></div>
        <div class="relative w-full max-w-sm bg-white rounded-2xl shadow-2xl p-6 text-center space-y-3">
            <div class="mx-auto w-14 h-14 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="9"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2"/>
                </svg>
            </div>
            <h3 class="text-base font-bold text-slate-900">Akun Sedang Diverifikasi</h3>
            <p class="text-xs text-slate-500 leading-relaxed">
                Terima kasih telah mendaftar. Data Anda sedang diperiksa oleh admin kampus.
                Anda <strong class="text-slate-700">belum dapat login</strong> sampai akun disetujui.
            </p>
            <button type="button" @click="pending = false"
                    class="w-full h-10 bg-blue-900 hover:bg-blue-800 text-white text-xs font-bold rounded-lg transition">
                Mengerti
            </button>
        </div>
    </div>
</div>
@endsection