@extends('layouts.app')

@section('title', 'Detail Pesanan ' . $order->invoice_number)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 pb-4">
        <div>
            <div class="text-xs font-bold uppercase tracking-wider text-emerald-800">Bukti Pesanan Resmi</div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">
                Invoice {{ $order->invoice_number }}
            </h1>
            <div class="text-xs text-slate-500 mt-0.5">
                Dibuat pada {{ $order->created_at->translatedFormat('d F Y, H:i') }} WIB
            </div>
        </div>

        <div>
            @if($order->status === 'confirmed')
                <span class="inline-flex items-center px-3 py-1.5 rounded text-xs font-bold uppercase tracking-wider bg-emerald-100 text-emerald-900 border border-emerald-300">
                    <span class="w-2 h-2 rounded-full bg-emerald-600 mr-2"></span>
                    Status: LUNAS / TERVERIFIKASI
                </span>
            @elseif($order->status === 'used')
                <span class="inline-flex items-center px-3 py-1.5 rounded text-xs font-bold uppercase tracking-wider bg-slate-200 text-slate-800 border border-slate-300">
                    <span class="w-2 h-2 rounded-full bg-slate-600 mr-2"></span>
                    Status: SUDAH DIGUNAKAN (CHECK-IN)
                </span>
            @elseif($order->status === 'rejected')
                <span class="inline-flex items-center px-3 py-1.5 rounded text-xs font-bold uppercase tracking-wider bg-rose-100 text-rose-900 border border-rose-300">
                    <span class="w-2 h-2 rounded-full bg-rose-600 mr-2"></span>
                    Status: DITOLAK / TIDAK VALID
                </span>
            @else
                <span class="inline-flex items-center px-3 py-1.5 rounded text-xs font-bold uppercase tracking-wider bg-amber-100 text-amber-900 border border-amber-300">
                    <span class="w-2 h-2 rounded-full bg-amber-600 mr-2"></span>
                    Status: {{ $order->payment_method === 'cod' ? 'MENUNGGU PEMBAYARAN LOKET' : 'MENUNGGU KONFIRMASI' }}
                </span>
            @endif
        </div>
    </div>

    <!-- Status Callout Card -->
    @if($order->isConfirmed())
        <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-6 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-base font-bold text-emerald-950">Pembayaran Sah & E-Ticket Telah Aktif</h2>
                    <p class="text-xs text-emerald-800 mt-1 leading-relaxed">
                        Pengelola Kebun Raya telah memverifikasi pembayaran Anda. Anda kini dapat membuka dan mencetak E-Ticket yang dilengkapi Kode QR resmi untuk pemindaian di pintu masuk.
                    </p>
                </div>
                
                <!-- LKPD Checklist #5: User menekan tombol "Download Tiket" -> Berhasil diakses -->
                <a 
                    href="{{ route('orders.eticket', $order->invoice_number) }}" 
                    class="inline-flex items-center justify-center text-center bg-emerald-800 hover:bg-emerald-900 text-white text-xs font-bold uppercase tracking-wider px-6 py-3 rounded transition shadow-xs whitespace-nowrap"
                >
                    Download / Cetak E-Ticket
                </a>
            </div>
        </div>
    @elseif($order->isRejected())
        <div class="bg-rose-50 border border-rose-200 rounded-lg p-6 space-y-4">
            <div>
                <h2 class="text-base font-bold text-rose-950">Bukti Pembayaran Ditolak oleh Pengelola</h2>
                <p class="text-xs text-rose-800 mt-1">
                    Alasan: <strong class="font-semibold">{{ $order->admin_notes ?? 'Bukti pembayaran tidak sesuai atau tidak terbaca dengan jelas.' }}</strong>
                </p>
            </div>

            <!-- Upload Re-submission Form -->
            <form action="{{ route('orders.uploadProof', $order->invoice_number) }}" method="POST" enctype="multipart/form-data" class="space-y-3 pt-2 border-t border-rose-200">
                @csrf
                <label for="payment_proof_retry" class="block text-xs font-bold uppercase tracking-wider text-rose-950">
                    Unggah Ulang Foto Bukti Transfer yang Sah
                </label>
                <div class="flex flex-col sm:flex-row gap-3">
                    <input 
                        type="file" 
                        id="payment_proof_retry" 
                        name="payment_proof" 
                        accept="image/*" 
                        required
                        class="text-xs text-slate-700 bg-white border border-rose-300 rounded p-1 flex-1"
                    >
                    <button type="submit" class="bg-rose-800 hover:bg-rose-900 text-white text-xs font-bold uppercase tracking-wider px-4 py-2 rounded transition">
                        Kirim Ulang Bukti
                    </button>
                </div>
            </form>
        </div>
    @else
        <!-- Pending Status Callout -->
        <div class="bg-amber-50 border border-amber-200 rounded-lg p-6 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <h2 class="text-base font-bold text-amber-950">
                        {{ $order->payment_method === 'cod' ? 'Pemesanan Tercatat - Siapkan Pembayaran Loket' : 'Bukti Pembayaran Tersimpan - Menunggu Konfirmasi Admin' }}
                    </h2>
                    <p class="text-xs text-amber-900 leading-relaxed">
                        @if($order->payment_method === 'cod')
                            Silakan tunjukkan nomor invoice <strong>{{ $order->invoice_number }}</strong> ke petugas loket saat berkunjung pada tanggal {{ $order->visit_date->translatedFormat('d F Y') }} untuk pelunasan tunai.
                        @else
                            Petugas pengelola Kebun Raya sedang memverifikasi keabsahan bukti transfer Anda. Status akan diperbarui secara otomatis setelah disetujui.
                        @endif
                    </p>
                </div>

                <!-- LKPD Checklist #3: User mencoba unduh tiket sebelum konfirmasi -> Tombol unduh TERKUNCI / DISABLED -->
                <div class="text-right">
                    <button 
                        type="button" 
                        disabled 
                        class="cursor-not-allowed opacity-50 bg-slate-400 text-slate-800 text-xs font-bold uppercase tracking-wider px-6 py-3 rounded select-none border border-slate-400"
                        title="E-Ticket terkunci hingga pembayaran terverifikasi oleh Admin"
                    >
                        Download E-Ticket (Terkunci)
                    </button>
                    <div class="text-[10px] text-amber-900 mt-1.5 font-medium">
                        Terkunci - Wajib Menunggu Konfirmasi Lunas Admin
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Information Breakdown Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Visitor & Visit Details -->
        <div class="bg-white rounded-lg border border-slate-200 p-6 shadow-2xs space-y-3">
            <div class="border-b border-slate-100 pb-2">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Informasi Kunjungan</h3>
            </div>

            <div class="space-y-2 text-xs">
                <div class="flex justify-between py-1 border-b border-slate-50">
                    <span class="text-slate-500">Tanggal Kunjungan:</span>
                    <span class="font-bold text-slate-900">{{ $order->visit_date->translatedFormat('d F Y') }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-50">
                    <span class="text-slate-500">Nama Pemesan:</span>
                    <span class="font-semibold text-slate-900">{{ $order->customer_name }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-50">
                    <span class="text-slate-500">Alamat Email:</span>
                    <span class="font-semibold text-slate-900">{{ $order->customer_email }}</span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-slate-500">Nomor Telepon:</span>
                    <span class="font-semibold text-slate-900">{{ $order->customer_phone ?? '-' }}</span>
                </div>
            </div>
        </div>

        <!-- Payment Details & Proof -->
        <div class="bg-white rounded-lg border border-slate-200 p-6 shadow-2xs space-y-3">
            <div class="border-b border-slate-100 pb-2">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Informasi Pembayaran</h3>
            </div>

            <div class="space-y-2 text-xs">
                <div class="flex justify-between py-1 border-b border-slate-50">
                    <span class="text-slate-500">Metode Pembayaran:</span>
                    <span class="font-bold text-slate-900 uppercase">{{ $order->payment_method === 'transfer' ? 'Transfer Bank' : 'COD / Bayar di Loket' }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-50">
                    <span class="text-slate-500">Total Pembayaran:</span>
                    <span class="font-black text-emerald-950 text-sm">{{ $order->formatted_total }}</span>
                </div>
                @if($order->confirmed_at)
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Waktu Verifikasi:</span>
                        <span class="font-semibold text-slate-900">{{ $order->confirmed_at->translatedFormat('d F Y, H:i') }} WIB</span>
                    </div>
                @endif
                @if($order->payment_proof)
                    <div class="py-1">
                        <span class="text-slate-500 block mb-1.5">Bukti Foto Transfer:</span>
                        <a href="{{ asset('storage/' . $order->payment_proof') }}" target="_blank" class="inline-block text-xs text-emerald-800 font-bold underline hover:text-emerald-900">
                            Lihat Foto Bukti Transfer Asli &rarr;
                        </a>
                    </div>
                @endif
            </div>
        </div>

    </div>

    <!-- Items Table -->
    <div class="bg-white rounded-lg border border-slate-200 p-6 shadow-2xs space-y-4">
        <div class="border-b border-slate-100 pb-2">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Rincian Tiket Masuk</h3>
        </div>

        <table class="w-full text-left text-xs text-slate-700 border-collapse">
            <thead>
                <tr class="border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider text-[11px] bg-slate-50">
                    <th class="py-2.5 px-3">Kategori Tiket</th>
                    <th class="py-2.5 px-3 text-right">Harga Satuan</th>
                    <th class="py-2.5 px-3 text-center">Jumlah</th>
                    <th class="py-2.5 px-3 text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($order->items as $item)
                    <tr>
                        <td class="py-3 px-3 font-semibold text-slate-900">{{ $item->ticket_name }}</td>
                        <td class="py-3 px-3 text-right">{{ $item->formatted_unit_price }}</td>
                        <td class="py-3 px-3 text-center font-bold">{{ $item->quantity }}</td>
                        <td class="py-3 px-3 text-right font-bold text-slate-900">{{ $item->formatted_subtotal }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="border-t-2 border-slate-300 bg-slate-50 font-bold">
                    <td colspan="3" class="py-3 px-3 text-right uppercase tracking-wider">Total Tagihan:</td>
                    <td class="py-3 px-3 text-right font-black text-emerald-950 text-base">{{ $order->formatted_total }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Bottom Action Links -->
    <div class="flex justify-between items-center pt-2">
        <a href="{{ route('orders.history') }}" class="text-xs font-bold uppercase tracking-wider text-slate-600 hover:text-slate-900 transition">
            &larr; Lihat Semua Riwayat Pesanan
        </a>

        @if($order->isConfirmed())
            <a href="{{ route('orders.eticket', $order->invoice_number) }}" class="bg-emerald-800 hover:bg-emerald-900 text-white text-xs font-bold uppercase tracking-wider px-5 py-2.5 rounded transition">
                Buka E-Ticket Digital &rarr;
            </a>
        @endif
    </div>

</div>
@endsection
