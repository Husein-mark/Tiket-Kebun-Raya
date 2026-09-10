<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Pemesanan Tiket Resmi') - Kebun Raya Indonesia</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: #ffffff !important; color: #000000 !important; }
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 antialiased min-h-screen flex flex-col font-sans selection:bg-emerald-900 selection:text-white">

    <!-- Top Announcement Bar -->
    <div class="bg-emerald-950 text-emerald-100 text-xs py-2 px-4 border-b border-emerald-900/60 no-print">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row justify-between items-center gap-1 font-medium">
            <div class="flex items-center gap-2">
                <span class="inline-block w-2 h-2 rounded-full bg-emerald-400"></span>
                <span>Portal Resmi Tiket Elektronik Kebun Raya Indonesia</span>
            </div>
            <div class="text-emerald-300">
                Jam Operasional: Senin - Minggu (08.00 - 16.00 WIB)
            </div>
        </div>
    </div>

    <!-- Main Navigation Header -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-xs no-print">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                
                <!-- Brand Title -->
                <a href="{{ route('home') }}" class="group flex flex-col">
                    <span class="text-xl font-bold tracking-tight text-emerald-950 group-hover:text-emerald-800 transition">
                        KEBUN RAYA INDONESIA
                    </span>
                    <span class="text-xs uppercase tracking-wider text-emerald-700 font-semibold">
                        Sistem Tiketing Konservasi & Wisata Edukasi
                    </span>
                </a>

                <!-- Navigation Links -->
                <nav class="flex items-center gap-6">
                    <a href="{{ route('home') }}" class="text-sm font-semibold {{ request()->routeIs('home') ? 'text-emerald-800 border-b-2 border-emerald-800 pb-1' : 'text-slate-600 hover:text-emerald-800' }} transition">
                        Pesan Tiket
                    </a>

                    @auth
                        <a href="{{ route('orders.history') }}" class="text-sm font-semibold {{ request()->routeIs('orders.history') ? 'text-emerald-800 border-b-2 border-emerald-800 pb-1' : 'text-slate-600 hover:text-emerald-800' }} transition">
                            Riwayat Pesanan
                        </a>

                        @if(Auth::user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-xs font-bold uppercase tracking-wider bg-slate-800 text-white px-3 py-1.5 rounded hover:bg-slate-900 transition">
                                Panel Admin
                            </a>
                        @endif

                        <div class="flex items-center gap-3 pl-3 border-l border-slate-200">
                            <div class="text-right">
                                <div class="text-xs font-bold text-slate-800">{{ Auth::user()->name }}</div>
                                <div class="text-[10px] text-slate-700 font-medium">{{ Auth::user()->email }}</div>
                            </div>
                            <form action="{{ route('logout') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-xs font-bold text-slate-500 hover:text-rose-700 uppercase tracking-wider px-2 py-1 border border-slate-300 rounded hover:border-rose-300 transition">
                                    Keluar
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="flex items-center gap-3">
                            <a href="{{ route('login') }}" class="text-sm font-semibold text-emerald-900 hover:text-emerald-700 px-3 py-1.5 transition">
                                Masuk
                            </a>
                            <a href="{{ route('register') }}" class="text-sm font-semibold bg-emerald-800 text-white px-4 py-2 rounded hover:bg-emerald-900 transition">
                                Daftar Akun
                            </a>
                        </div>
                    @endauth
                </nav>

            </div>
        </div>
    </header>

    <!-- Global Notifications & Flash Messages -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full no-print">
        @if(session('success'))
            <div class="bg-emerald-50 border-l-4 border-emerald-600 p-4 rounded-r shadow-xs mb-4">
                <div class="flex">
                    <div class="text-sm font-semibold text-emerald-900">
                        {{ session('success') }}
                    </div>
                </div>
            </div>
        @endif

        @if(session('info'))
            <div class="bg-blue-50 border-l-4 border-blue-600 p-4 rounded-r shadow-xs mb-4">
                <div class="flex">
                    <div class="text-sm font-semibold text-blue-900">
                        {{ session('info') }}
                    </div>
                </div>
            </div>
        @endif

        @if(session('warning'))
            <div class="bg-amber-50 border-l-4 border-amber-600 p-4 rounded-r shadow-xs mb-4">
                <div class="flex">
                    <div class="text-sm font-semibold text-amber-900">
                        {{ session('warning') }}
                    </div>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-rose-50 border-l-4 border-rose-600 p-4 rounded-r shadow-xs mb-4">
                <div class="flex">
                    <div class="text-sm font-semibold text-rose-900">
                        {{ session('error') }}
                    </div>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="bg-rose-50 border-l-4 border-rose-600 p-4 rounded-r shadow-xs mb-4">
                <div class="text-sm font-bold text-rose-900 mb-1">Terdapat beberapa data yang perlu diperbaiki:</div>
                <ul class="list-disc list-inside text-xs text-rose-800 space-y-0.5">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <!-- Main Content Area -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 mt-16 no-print">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <div class="font-bold text-slate-900 tracking-tight text-base mb-2">KEBUN RAYA INDONESIA</div>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Pusat konservasi tumbuhan ex-situ, riset botani, dan kawasan rekreasi alam edukatif terkemuka di Indonesia. Menjaga keanekaragaman flora nusantara untuk generasi mendatang.
                    </p>
                </div>
                <div>
                    <div class="font-bold text-slate-900 tracking-tight text-base mb-2">KETENTUAN KUNJUNGAN</div>
                    <ul class="text-xs text-slate-600 space-y-1.5">
                        <li>Tiket berlaku sesuai tanggal kunjungan yang tertera pada invoice.</li>
                        <li>Anak di bawah usia 3 tahun tidak dikenakan tiket masuk.</li>
                        <li>Dilarang merusak vegetasi, memetik tanaman, dan membuang sampah sembarangan.</li>
                        <li>Patuhi seluruh instruksi petugas konservasi di lapangan.</li>
                    </ul>
                </div>
                <div>
                    <div class="font-bold text-slate-900 tracking-tight text-base mb-2">LAYANAN & INFORMASI</div>
                    <p class="text-xs text-slate-600 leading-relaxed mb-2">
                        Pusat Informasi Pengunjung & Pelayanan Loket:<br>
                        Email: info@kebunraya.id | WhatsApp: 0812-3456-7890
                    </p>
                    <div class="text-[11px] text-slate-600">
                        Proyek Pengembangan Sistem Tiketing Digital Kebun Raya (LKPD)
                    </div>
                </div>
            </div>
            <div class="mt-8 pt-6 border-t border-slate-200 text-center text-xs text-slate-600">
                &copy; {{ date('Y') }} Pengelola Kebun Raya Indonesia. Seluruh hak cipta dilindungi undang-undang.
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
