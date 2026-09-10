@extends('layouts.app')

@section('title', 'E-Ticket Resmi ' . $order->invoice_number)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Top Action Bar (Hidden on Print) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-4 rounded-lg border border-slate-200 shadow-2xs no-print">
        <div>
            <div class="text-xs font-bold uppercase tracking-wider text-emerald-800">E-Ticket Sah Terbit</div>
            <div class="text-sm font-bold text-slate-900">Tiket Elektronik Kebun Raya Indonesia</div>
        </div>

        <div class="flex items-center gap-3">
            <button 
                onclick="window.print()" 
                class="inline-flex items-center bg-emerald-800 hover:bg-emerald-900 text-white text-xs font-bold uppercase tracking-wider px-4 py-2.5 rounded transition shadow-xs cursor-pointer"
            >
                Cetak / Simpan E-Ticket (PDF)
            </button>

            <a 
                href="{{ $verificationUrl }}" 
                target="_blank"
                class="inline-flex items-center bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold uppercase tracking-wider px-4 py-2.5 rounded border border-slate-300 transition"
            >
                Uji Scan Verifikasi Online
            </a>
        </div>
    </div>

    <!-- Official Printable Ticket Card Container -->
    <div class="bg-white rounded-xl border-2 border-emerald-900/40 shadow-sm overflow-hidden text-slate-900 print:border-black print:shadow-none">
        
        <!-- Header Ribbon -->
        <div class="bg-emerald-950 text-white px-6 py-5 border-b-2 border-emerald-800 flex flex-col sm:flex-row justify-between sm:items-center gap-4">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-widest text-emerald-300 bg-emerald-900 px-2 py-0.5 rounded border border-emerald-700/60">
                    Tiket Resmi Masuk Wisata & Edukasi
                </span>
                <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white mt-1">
                    KEBUN RAYA INDONESIA
                </h1>
                <div class="text-[11px] text-emerald-200">
                    Lembaga Konservasi Tumbuhan Ex-Situ Nasional
                </div>
            </div>

            <div class="text-left sm:text-right border-t sm:border-t-0 pt-2 sm:pt-0 border-emerald-800/80">
                <div class="text-[10px] uppercase tracking-wider text-emerald-300 font-bold">Nomor Invoice Resmi</div>
                <div class="font-mono text-base font-black text-white tracking-wider">
                    {{ $order->invoice_number }}
                </div>
                <div class="text-[10px] text-emerald-300 font-medium">
                    Status: LUNAS & TERVERIFIKASI
                </div>
            </div>
        </div>

        <!-- Ticket Body -->
        <div class="p-6 sm:p-8 space-y-6">

            <!-- Primary Pass Grid: Details & QR Code -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                
                <!-- Left 2 Cols: Visitor & Visit Information -->
                <div class="md:col-span-2 space-y-4">
                    
                    <div class="bg-slate-50 border border-slate-200 p-4 rounded-lg">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">
                            Tanggal Kunjungan Resmi
                        </div>
                        <div class="text-xl sm:text-2xl font-black text-emerald-950">
                            {{ $order->visit_date->translatedFormat('l, d F Y') }}
                        </div>
                        <div class="text-xs text-emerald-800 font-semibold mt-0.5">
                            Pintu Gerbang Buka: 08.00 - 16.00 WIB
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 text-xs">
                        <div class="border-b border-slate-100 pb-2">
                            <span class="text-slate-500 block text-[10px] uppercase font-bold tracking-wider">Nama Pemesan</span>
                            <span class="font-bold text-slate-900 text-sm">{{ $order->customer_name }}</span>
                        </div>

                        <div class="border-b border-slate-100 pb-2">
                            <span class="text-slate-500 block text-[10px] uppercase font-bold tracking-wider">Total Peserta</span>
                            <span class="font-bold text-slate-900 text-sm">{{ $order->total_tickets }} Orang / Unit</span>
                        </div>

                        <div class="border-b border-slate-100 pb-2">
                            <span class="text-slate-500 block text-[10px] uppercase font-bold tracking-wider">Email Terdaftar</span>
                            <span class="font-semibold text-slate-800">{{ $order->customer_email }}</span>
                        </div>

                        <div class="border-b border-slate-100 pb-2">
                            <span class="text-slate-500 block text-[10px] uppercase font-bold tracking-wider">Metode Bayar</span>
                            <span class="font-bold uppercase text-emerald-900">{{ $order->payment_method === 'transfer' ? 'Transfer Bank' : 'COD (Loket)' }}</span>
                        </div>
                    </div>

                </div>

                <!-- Right 1 Col: Prominent QR Code Box -->
                <div class="md:col-span-1 flex flex-col items-center justify-center p-4 bg-slate-50 border border-slate-200 rounded-lg text-center space-y-2">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-emerald-900">
                        Pindai Untuk Verifikasi
                    </div>
                    
                    <!-- SVG Vector QR Code rendered in pure PHP without external dependencies -->
                    <div class="bg-white p-2 rounded border border-slate-300 shadow-2xs">
                        {!! $qrCodeSvg !!}
                    </div>

                    <div class="text-[10px] font-mono text-slate-500 break-all leading-tight">
                        ID: {{ substr($order->verification_code, 0, 16) }}...
                    </div>
                    
                    <div class="text-[10px] text-slate-600 font-medium leading-tight">
                        Tunjukkan Kode QR ini kepada petugas pemindai di pintu gerbang masuk.
                    </div>
                </div>

            </div>

            <!-- Breakdown of Tickets -->
            <div class="border-t border-dashed border-slate-300 pt-6">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-3">
                    Rincian Kategori Karcis Masuk
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700 border-collapse">
                        <thead>
                            <tr class="bg-slate-100 text-slate-600 font-bold uppercase text-[10px]">
                                <th class="py-2 px-3">Kategori</th>
                                <th class="py-2 px-3 text-right">Tarif</th>
                                <th class="py-2 px-3 text-center">Jumlah</th>
                                <th class="py-2 px-3 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($order->items as $item)
                                <tr>
                                    <td class="py-2.5 px-3 font-semibold text-slate-900">{{ $item->ticket_name }}</td>
                                    <td class="py-2.5 px-3 text-right">{{ $item->formatted_unit_price }}</td>
                                    <td class="py-2.5 px-3 text-center font-bold">{{ $item->quantity }}</td>
                                    <td class="py-2.5 px-3 text-right font-bold text-slate-900">{{ $item->formatted_subtotal }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="border-t-2 border-slate-300 bg-slate-50 font-bold">
                                <td colspan="3" class="py-2.5 px-3 text-right text-xs uppercase tracking-wider text-slate-800">
                                    Total Lunas:
                                </td>
                                <td class="py-2.5 px-3 text-right text-emerald-950 font-black text-sm">
                                    {{ $order->formatted_total }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Terms and Entry Regulations -->
            <div class="border-t border-slate-200 pt-4 text-[11px] text-slate-500 space-y-1 leading-relaxed">
                <div class="font-bold uppercase tracking-wider text-slate-700 mb-1">Ketentuan Penggunaan Tiket:</div>
                <p>1. Tiket ini hanya sah untuk 1 (satu) kali pemindaian masuk pada tanggal yang tertera.</p>
                <p>2. Pengunjung wajib menjaga kelestarian flora, tidak memetik tanaman, serta membuang sampah pada tempatnya.</p>
                <p>3. Pengelola berhak menolak tiket yang sudah dipindai sebelumnya atau dipalsukan.</p>
                <p>4. Untuk verifikasi keaslian mandiri, Anda dapat memindai kode QR menggunakan kamera ponsel Anda.</p>
            </div>

            <!-- Bottom Security Barcode Simulation Strip -->
            <div class="border-t border-slate-200 pt-4 flex flex-col sm:flex-row justify-between items-center text-[10px] text-slate-400 font-mono gap-2">
                <div>AUTHENTIC DIGITAL PASS // KEBUN RAYA INDONESIA</div>
                <div>HASH: {{ strtoupper(md5($order->verification_code . $order->invoice_number)) }}</div>
            </div>

        </div>

    </div>

    <!-- Back Navigation (Hidden on Print) -->
    <div class="text-center pt-2 no-print">
        <a href="{{ route('orders.show', $order->invoice_number) }}" class="text-xs font-bold uppercase tracking-wider text-slate-600 hover:text-slate-900 transition">
            &larr; Kembali ke Detail Pesanan
        </a>
    </div>

</div>
@endsection
