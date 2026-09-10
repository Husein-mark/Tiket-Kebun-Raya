@extends('layouts.app')

@section('title', 'Masuk Akun')

@section('content')
<div class="max-w-md mx-auto space-y-6">

    <!-- Notice if redirected from guest booking -->
    @if(session()->has('booking_draft'))
        <div class="bg-emerald-50 border-2 border-emerald-800 p-4 rounded-lg space-y-2">
            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-900 bg-emerald-200/70 px-2 py-0.5 rounded">
                Draf Pemesanan Tersimpan
            </span>
            <div class="text-xs font-bold text-emerald-950">
                Pemesanan tiket Anda telah tersimpan secara aman.
            </div>
            <p class="text-xs text-emerald-800 leading-relaxed">
                Silakan masuk ke akun Anda atau daftar akun baru di bawah untuk menyelesaikan proses pembayaran & verifikasi E-Ticket.
            </p>
        </div>
    @endif

    <div class="bg-white rounded-lg border border-slate-200 p-8 shadow-xs space-y-6">
        
        <div>
            <span class="text-xs font-bold uppercase tracking-wider text-emerald-800">Autentikasi Akun</span>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-0.5">
                Masuk ke Portal Tiket
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Gunakan email dan kata sandi yang telah terdaftar pada sistem Kebun Raya.
            </p>
        </div>

        <form action="{{ route('login') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                    Alamat Email
                </label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="{{ old('email') }}" 
                    required 
                    autofocus
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded font-medium text-slate-800 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-800"
                >
            </div>

            <div>
                <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                    Kata Sandi
                </label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required 
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded font-medium text-slate-800 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-800"
                >
            </div>

            <div class="flex items-center justify-between text-xs pt-1">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" name="remember" class="text-emerald-800 focus:ring-emerald-800 rounded">
                    <span class="text-slate-600">Ingat sesi saya</span>
                </label>
            </div>

            <button 
                type="submit" 
                class="w-full bg-emerald-800 hover:bg-emerald-900 text-white font-bold text-sm uppercase tracking-wider py-3 px-4 rounded transition shadow-xs cursor-pointer"
            >
                Masuk Sekarang
            </button>
        </form>

        <div class="border-t border-slate-100 pt-4 text-center text-xs text-slate-600">
            Belum memiliki akun pengunjung? 
            <a href="{{ route('register') }}" class="font-bold text-emerald-900 hover:underline">
                Daftar Akun Baru
            </a>
        </div>

        <!-- Reference Accounts Callout (Sesuai Seeder Akun Admin & Pembeli) -->
        <div class="bg-slate-50 border border-slate-200 rounded p-3 text-[11px] text-slate-500 space-y-1">
            <div class="font-bold text-slate-700 uppercase">Akun Default Database Seeder:</div>
            <div>Admin: <code class="font-mono text-slate-800">admin@kebunraya.id</code> / <code class="font-mono text-slate-800">password</code></div>
            <div>Pembeli: <code class="font-mono text-slate-800">pembeli@kebunraya.id</code> / <code class="font-mono text-slate-800">password</code></div>
        </div>

    </div>

</div>
@endsection
