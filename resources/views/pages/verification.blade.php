@extends('layouts.app')

@section('title', 'Verifikasi Keaslian Tiket Resmi')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    @if(!$order)
        <!-- Invalid Ticket State -->
        <div class="bg-white rounded-lg border border-rose-200 p-8 text-center space-y-4 shadow-xs">
            <div class="inline-block px-3 py-1 bg-rose-100 text-rose-800 text-xs font-bold uppercase tracking-wider rounded border border-rose-300">
                Verifikasi Gagal
            </div>
            <h1 class="text-2xl font-black text-rose-950">
                Tiket Tidak Ditemukan / Tidak Valid
            </h1>
            <p class="text-xs text-rose-700 max-w-md mx-auto leading-relaxed">
                Kode verifikasi <span class="font-mono bg-rose-50 px-1 py-0.5 rounded">{{ $verificationCode }}</span> tidak terdaftar pada basis data resmi Kebun Raya Indonesia. Pastikan Anda memindai Kode QR resmi dari tiket yang sah.
            </p>
            <div class="pt-4">
                <a href="{{ route('home') }}" class="bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold uppercase tracking-wider px-5 py-2.5 rounded transition">
                    Kembali ke Beranda
                </a>
            </div>
        </div>
    @else
        <!-- Valid Ticket Verification Card -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            
            <!-- Status Header Banner -->
            @if($order->status === 'confirmed')
                <div class="bg-emerald-800 text-white p-6 sm:p-8 text-center border-b-4 border-emerald-950 space-y-2">
                    <span class="inline-block text-[11px] font-black uppercase tracking-widest bg-emerald-950 text-emerald-200 px-3 py-1 rounded border border-emerald-700/60">
                        Hasil Pemindaian QR Code Resmi
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
                        TIKET RESMI TERVERIFIKASI
                    </h1>
                    <p class="text-xs text-emerald-100 max-w-md mx-auto leading-relaxed">
                        Data pemesanan ini sah terdaftar pada sistem tiket elektronik Kebun Raya Indonesia.
                    </p>
                    <div class="pt-2">
                        <span class="inline-block bg-white text-emerald-950 font-black text-xs uppercase tracking-wider px-3 py-1 rounded shadow-2xs">
                            Status: VALID - BELUM DIGUNAKAN
                        </span>
                    </div>
                </div>
            @elseif($order->status === 'used')
                <div class="bg-slate-800 text-white p-6 sm:p-8 text-center border-b-4 border-slate-950 space-y-2">
                    <span class="inline-block text-[11px] font-black uppercase tracking-widest bg-slate-950 text-slate-300 px-3 py-1 rounded border border-slate-700">
                        Hasil Pemindaian QR Code Resmi
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
                        TIKET RESMI - SUDAH DIGUNAKAN
                    </h1>
                    <p class="text-xs text-slate-300 max-w-md mx-auto leading-relaxed">
                        Tiket ini sah dan telah dilakukan pemindaian check-in di pintu gerbang masuk.
                    </p>
                    <div class="pt-2">
                        <span class="inline-block bg-slate-200 text-slate-900 font-bold text-xs uppercase tracking-wider px-3 py-1 rounded">
                            Check-In: {{ $order->used_at?->translatedFormat('d F Y, H:i') ?? '-' }} WIB
                        </span>
                    </div>
                </div>
            @elseif($order->status === 'rejected')
                <div class="bg-rose-800 text-white p-6 sm:p-8 text-center border-b-4 border-rose-950 space-y-2">
                    <span class="inline-block text-[11px] font-black uppercase tracking-widest bg-rose-950 text-rose-200 px-3 py-1 rounded">
                        Hasil Pemindaian QR Code Resmi
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
                        TIKET DIBATALKAN / TIDAK VALID
                    </h1>
                    <p class="text-xs text-rose-200 max-w-md mx-auto">
                        Pembayaran tiket ini tidak disetujui atau dibatalkan oleh pengelola.
                    </p>
                </div>
            @else
                <div class="bg-amber-700 text-white p-6 sm:p-8 text-center border-b-4 border-amber-900 space-y-2">
                    <span class="inline-block text-[11px] font-black uppercase tracking-widest bg-amber-950 text-amber-200 px-3 py-1 rounded">
                        Hasil Pemindaian QR Code Resmi
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
                        MENUNGGU KONFIRMASI PEMBAYARAN
                    </h1>
                    <p class="text-xs text-amber-100 max-w-md mx-auto">
                        Tiket masih berstatus pending dan belum berstatus Lunas.
                    </p>
                </div>
            @endif

            <!-- Verification Metadata Details -->
            <div class="p-6 sm:p-8 space-y-6">
                
                <div class="border-b border-slate-200 pb-3">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">
                        Rincian Tiket Masuk Terverifikasi
                    </h2>
                </div>

                <div class="space-y-3 text-xs">
                    <div class="flex justify-between py-1.5 border-b border-slate-100">
                        <span class="text-slate-500">Nomor Invoice:</span>
                        <span class="font-mono font-bold text-slate-900 text-sm">{{ $order->invoice_number }}</span>
                    </div>

                    <div class="flex justify-between py-1.5 border-b border-slate-100">
                        <span class="text-slate-500">Tanggal Kunjungan:</span>
                        <span class="font-black text-emerald-950 text-sm">{{ $order->visit_date->translatedFormat('l, d F Y') }}</span>
                    </div>

                    <div class="flex justify-between py-1.5 border-b border-slate-100">
                        <span class="text-slate-500">Nama Pengunjung:</span>
                        <span class="font-bold text-slate-900">{{ $order->customer_name }}</span>
                    </div>

                    <div class="flex justify-between py-1.5 border-b border-slate-100">
                        <span class="text-slate-500">Total Kuantitas:</span>
                        <span class="font-bold text-slate-900">{{ $order->total_tickets }} Orang / Kendaraan</span>
                    </div>

                    <div class="flex justify-between py-1.5 border-b border-slate-100">
                        <span class="text-slate-500">Metode Pembayaran:</span>
                        <span class="font-bold uppercase text-slate-900">{{ $order->payment_method === 'transfer' ? 'Transfer Bank' : 'COD / Loket' }}</span>
                    </div>

                    <div class="flex justify-between py-1.5 border-b border-slate-100">
                        <span class="text-slate-500">Status Pembayaran:</span>
                        <span class="font-bold text-emerald-900">{{ $order->status_label }}</span>
                    </div>

                    @if($order->confirmed_at)
                        <div class="flex justify-between py-1.5 border-b border-slate-100">
                            <span class="text-slate-500">Waktu Verifikasi Admin:</span>
                            <span class="font-semibold text-slate-800">{{ $order->confirmed_at->translatedFormat('d F Y, H:i') }} WIB</span>
                        </div>
                    @endif
                </div>

                <!-- Breakdown of Items -->
                <div class="space-y-2 pt-2">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Daftar Tiket:</div>
                    <div class="bg-slate-50 rounded-lg p-3 border border-slate-200 divide-y divide-slate-200 text-xs">
                        @foreach($order->items as $item)
                            <div class="flex justify-between py-1.5">
                                <span class="font-semibold text-slate-800">{{ $item->ticket_name }}</span>
                                <span class="font-bold text-slate-900">{{ $item->quantity }} Tiket</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Petugas / Admin Action: Check-In Button -->
                @auth
                    @if(Auth::user()->isAdmin() && $order->status === 'confirmed')
                        <form action="{{ route('ticket.checkin', $order->verification_code) }}" method="POST" class="pt-4 border-t border-slate-200">
                            @csrf
                            <div class="bg-emerald-50 border border-emerald-300 p-4 rounded-lg space-y-3">
                                <div class="text-xs font-bold uppercase tracking-wider text-emerald-950">
                                    Aksi Petugas Pintu Gerbang:
                                </div>
                                <p class="text-xs text-emerald-800">
                                    Tekan tombol di bawah untuk memvalidasi masuk pengunjung ke dalam kawasan Kebun Raya.
                                </p>
                                <button type="submit" class="w-full bg-emerald-800 hover:bg-emerald-900 text-white text-xs font-bold uppercase tracking-wider py-3 px-4 rounded transition shadow-xs">
                                    Verifikasi Masuk & Check-In Pengunjung
                                </button>
                            </div>
                        </form>
                    @endif
                @endauth

                <!-- Institutional Footer Notice -->
                <div class="border-t border-slate-200 pt-4 text-center text-[11px] text-slate-500 leading-relaxed">
                    Sistem Otentikasi Tiket Digital Resmi &bull; Kebun Raya Indonesia &bull; Dilindungi Keamanan Server Terintegrasi
                </div>

            </div>

        </div>
    @endif

    <div class="text-center">
        <a href="{{ route('home') }}" class="text-xs font-bold uppercase tracking-wider text-slate-600 hover:text-slate-900 transition">
            &larr; Halaman Utama Pemesanan
        </a>
    </div>

</div>
@endsection
