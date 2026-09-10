<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel Pengelola') - Kebun Raya Indonesia</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-900 antialiased min-h-screen flex flex-col font-sans selection:bg-slate-800 selection:text-white">

    <!-- Top Admin Bar -->
    <header class="bg-slate-900 text-slate-200 border-b border-slate-800 sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                
                <!-- Brand Title -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.dashboard') }}" class="font-bold text-white tracking-tight text-base hover:text-emerald-400 transition">
                        KEBUN RAYA INDONESIA
                    </a>
                    <span class="text-[11px] font-bold uppercase tracking-wider bg-emerald-950 text-emerald-300 border border-emerald-800 px-2 py-0.5 rounded">
                        Pengelola
                    </span>
                </div>

                <!-- Admin Navigation Menu -->
                <nav class="hidden md:flex items-center gap-6">
                    <a href="{{ route('admin.dashboard') }}" class="text-sm font-semibold {{ request()->routeIs('admin.dashboard') ? 'text-white border-b-2 border-emerald-500 pb-1' : 'text-slate-400 hover:text-white' }} transition">
                        Dashboard
                    </a>
                    <a href="{{ route('admin.orders.index') }}" class="text-sm font-semibold {{ request()->routeIs('admin.orders.*') ? 'text-white border-b-2 border-emerald-500 pb-1' : 'text-slate-400 hover:text-white' }} transition">
                        Pesanan & Verifikasi
                    </a>
                    <a href="{{ route('admin.tickets.index') }}" class="text-sm font-semibold {{ request()->routeIs('admin.tickets.*') ? 'text-white border-b-2 border-emerald-500 pb-1' : 'text-slate-400 hover:text-white' }} transition">
                        Produk Tiket
                    </a>
                    <a href="{{ route('home') }}" class="text-xs font-semibold text-slate-400 hover:text-slate-200 border border-slate-700 px-2.5 py-1 rounded transition">
                        Lihat Portal Pengunjung
                    </a>
                </nav>

                <!-- Admin User & Logout -->
                <div class="flex items-center gap-3">
                    <div class="text-right hidden sm:block">
                        <div class="text-xs font-bold text-white">{{ Auth::user()->name }}</div>
                        <div class="text-[10px] text-slate-400">{{ Auth::user()->email }}</div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="text-xs font-bold text-slate-300 hover:text-rose-400 uppercase tracking-wider px-2.5 py-1 border border-slate-700 rounded hover:border-rose-400 transition">
                            Keluar
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </header>

    <!-- Global Notifications & Flash Messages -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
        @if(session('success'))
            <div class="bg-emerald-50 border-l-4 border-emerald-600 p-4 rounded-r shadow-xs mb-4">
                <div class="text-sm font-semibold text-emerald-900">
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if(session('info'))
            <div class="bg-blue-50 border-l-4 border-blue-600 p-4 rounded-r shadow-xs mb-4">
                <div class="text-sm font-semibold text-blue-900">
                    {{ session('info') }}
                </div>
            </div>
        @endif

        @if(session('warning'))
            <div class="bg-amber-50 border-l-4 border-amber-600 p-4 rounded-r shadow-xs mb-4">
                <div class="text-sm font-semibold text-amber-900">
                    {{ session('warning') }}
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-rose-50 border-l-4 border-rose-600 p-4 rounded-r shadow-xs mb-4">
                <div class="text-sm font-semibold text-rose-900">
                    {{ session('error') }}
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="bg-rose-50 border-l-4 border-rose-600 p-4 rounded-r shadow-xs mb-4">
                <div class="text-sm font-bold text-rose-900 mb-1">Periksa isian formulir:</div>
                <ul class="list-disc list-inside text-xs text-rose-800 space-y-0.5">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <!-- Main Admin Content Area -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @yield('content')
    </main>

    <footer class="bg-white border-t border-slate-200 mt-12 py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center text-xs text-slate-500">
            <div>Portal Administrator & Verifikasi Tiket Kebun Raya Indonesia</div>
            <div>Status Sistem: Terhubung & Aktif</div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
