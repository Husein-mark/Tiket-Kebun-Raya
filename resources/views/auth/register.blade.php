@extends('layouts.app')

@section('title', 'Pendaftaran Akun Pengunjung')

@section('content')
<div class="max-w-md mx-auto space-y-6">

    @if(session()->has('booking_draft'))
        <div class="bg-emerald-50 border-2 border-emerald-800 p-4 rounded-lg space-y-1 text-emerald-950">
            <span class="text-[10px] font-bold uppercase tracking-wider bg-emerald-200/70 text-emerald-900 px-2 py-0.5 rounded">
                Draf Pemesanan Tersimpan
            </span>
            <div class="text-xs font-bold pt-1">
                Lengkapi pendaftaran di bawah ini untuk menyelesaikan pembelian tiket Anda.
            </div>
        </div>
    @endif

    <div class="bg-white rounded-lg border border-slate-200 p-8 shadow-xs space-y-6">
        
        <div>
            <span class="text-xs font-bold uppercase tracking-wider text-emerald-800">Registrasi Pengunjung</span>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-0.5">
                Buat Akun Baru
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Daftarkan data diri Anda untuk kemudahan transaksi dan akses E-Ticket resmi.
            </p>
        </div>

        <form action="{{ route('register') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                    Nama Lengkap Sesuai KTP / Paspor
                </label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    value="{{ old('name') }}" 
                    required 
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded font-medium text-slate-800 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-800"
                >
            </div>

            <div>
                <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                    Alamat Email Aktif
                </label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="{{ old('email') }}" 
                    required 
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded font-medium text-slate-800 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-800"
                >
            </div>

            <div>
                <label for="phone" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                    Nomor WhatsApp / Seluler
                </label>
                <input 
                    type="tel" 
                    id="phone" 
                    name="phone" 
                    value="{{ old('phone') }}" 
                    placeholder="Contoh: 08123456789"
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded font-medium text-slate-800 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-800"
                >
            </div>

            <div>
                <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                    Kata Sandi (Minimal 6 Karakter)
                </label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required 
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded font-medium text-slate-800 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-800"
                >
            </div>

            <div>
                <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                    Konfirmasi Kata Sandi
                </label>
                <input 
                    type="password" 
                    id="password_confirmation" 
                    name="password_confirmation" 
                    required 
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded font-medium text-slate-800 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-800"
                >
            </div>

            <button 
                type="submit" 
                class="w-full bg-emerald-800 hover:bg-emerald-900 text-white font-bold text-sm uppercase tracking-wider py-3 px-4 rounded transition shadow-xs cursor-pointer"
            >
                Daftar & Lanjutkan
            </button>
        </form>

        <div class="border-t border-slate-100 pt-4 text-center text-xs text-slate-600">
            Sudah memiliki akun? 
            <a href="{{ route('login') }}" class="font-bold text-emerald-900 hover:underline">
                Masuk di Sini
            </a>
        </div>

    </div>

</div>
@endsection
