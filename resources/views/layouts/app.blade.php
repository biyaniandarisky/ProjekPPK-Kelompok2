<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Sistem Fasilitas Kampus') }}</title>
    <!-- Tailwind CSS CDN / Vite -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        navy: '#1e3a8a',
                        emerald: '#10b981',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-50 text-slate-900 flex flex-col min-h-screen">
    <!-- Navbar Bergaya Traveloka -->
    <header class="bg-blue-900 text-white shadow-md sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="{{ route('landing') }}" class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white text-blue-900 font-black text-xl flex items-center justify-center shadow">K</div>
                <div>
                    <div class="font-extrabold text-base leading-tight">KampusReserve</div>
                    <div class="text-[10px] text-blue-200">Sistem Fasilitas Terpadu</div>
                </div>
            </a>

            <nav class="flex items-center gap-3 text-xs font-bold">
                <a href="{{ route('landing') }}" class="px-3 py-2 rounded-lg hover:bg-blue-800 transition">Cari Fasilitas</a>
                @guest
                    <a href="{{ route('login') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl shadow transition">Login Satu Pintu</a>
                @else
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="px-3 py-2 bg-blue-950 rounded-lg">Panel Admin</a>
                    @elseif(auth()->user()->isPetugas())
                        <a href="{{ route('petugas.dashboard') }}" class="px-3 py-2 bg-emerald-600 rounded-lg">Panel Petugas</a>
                    @else
                        <a href="{{ route('pengguna.dashboard') }}" class="px-3 py-2 bg-blue-800 rounded-lg">Panel Mahasiswa</a>
                    @endif
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-3 py-2 bg-rose-600/80 hover:bg-rose-600 rounded-lg text-white transition">Keluar</button>
                    </form>
                @endguest
            </nav>
        </div>
    </header>

    <!-- Flash Alert -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
        @if(session('success'))
            <div class="p-4 bg-emerald-100 border border-emerald-300 text-emerald-900 rounded-xl text-xs font-bold mb-3">
                {{ session('success') }}
            </div>
        @endif
        @if(session('info'))
            <div class="p-4 bg-blue-100 border border-blue-300 text-blue-900 rounded-xl text-xs font-bold mb-3">
                {{ session('info') }}
            </div>
        @endif
        @if($errors->any())
            <div class="p-4 bg-rose-100 border border-rose-300 text-rose-900 rounded-xl text-xs font-bold mb-3">
                @foreach($errors->all() as $err)
                    <div>• {{ $err }}</div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Main Content -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-blue-950 text-white py-8 mt-12 text-xs border-t border-blue-900">
        <div class="max-w-7xl mx-auto px-4 text-center text-blue-300 space-y-2">
            <p>&copy; {{ date('Y') }} Sistem Fasilitas Kampus Terpadu. Dibangun dengan Laravel 11/12, MySQL, & Tailwind CSS.</p>
        </div>
    </footer>
</body>
</html>