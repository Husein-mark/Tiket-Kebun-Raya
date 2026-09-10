@extends('layouts.admin')

@section('title', 'Verifikasi Transaksi ' . $order->invoice_number)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 pb-4">
        <div>
            <div class="text-xs font-bold uppercase tracking-wider text-emerald-800">Verifikasi Manual Admin (Tahap 3 LKPD)</div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">
                Transaksi: {{ $order->invoice_number }}
            </h1>
            <div class="text-xs text-slate-500 mt-0.5">
                Dipesan pada {{ $order->created_at->translatedFormat('d F Y, H:i') }} WIB
            </div>
        </div>

        <div>
            @if($order->status === 'confirmed')
                <span class="inline-block px-3 py-1.5 rounded text-xs font-bold uppercase tracking-wider bg-emerald-100 text-emerald-900 border border-emerald-300">
                    LUNAS / TERVERIFIKASI
                </span>
            @elseif($order->status === 'used')
                <span class="inline-block px-3 py-1.5 rounded text-xs font-bold uppercase tracking-wider bg-slate-200 text-slate-800 border border-slate-300">
                    SUDAH DIGUNAKAN (CHECK-IN)
                </span>
            @elseif($order->status === 'rejected')
                <span class="inline-block px-3 py-1.5 rounded text-xs font-bold uppercase tracking-wider bg-rose-100 text-rose-900 border border-rose-300">
                    DITOLAK
                </span>
            @else
                <span class="inline-block px-3 py-1.5 rounded text-xs font-bold uppercase tracking-wider bg-amber-100 text-amber-900 border border-amber-300">
                    MENUNGGU VERIFIKASI ADMIN
                </span>
            @endif
        </div>
    </div>

    <!-- Main Grid: Payment Proof & Order Breakdown -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Left: Payment Proof Image Section -->
        <div class="bg-white rounded-lg border border-slate-200 p-6 shadow-2xs space-y-4">
            <div class="border-b border-slate-100 pb-2 flex justify-between items-center">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-700">Bukti Pembayaran Pengunjung</h2>
                <span class="text-[11px] uppercase font-bold text-slate-500">
                    {{ $order->payment_method === 'transfer' ? 'Transfer Bank' : 'COD (Loket)' }}
                </span>
            </div>

            @if($order->payment_proof)
                <div class="space-y-3">
                    <div class="border border-slate-200 rounded-lg overflow-hidden bg-slate-50 p-2">
                        <img 
                            src="{{ asset('storage/' . $order->payment_proof) }}" 
                            alt="Bukti Transfer {{ $order->invoice_number }}"
                            class="w-full max-h-96 object-contain rounded"
                        >
                    </div>
                    <div class="text-center">
                        <a 
                            href="{{ asset('storage/' . $order->payment_proof) }}" 
                            target="_blank" 
                            class="text-xs font-bold text-emerald-800 hover:text-emerald-950 underline"
                        >
                            Buka Foto Ukuran Penuh di Tab Baru &rarr;
                        </a>
                    </div>
                </div>
            @else
                <div class="bg-slate-50 border border-slate-200 rounded-lg p-8 text-center text-xs text-slate-500">
                    @if($order->payment_method === 'cod')
                        <div class="font-bold text-slate-700 mb-1">Metode Pembayaran di Loket (COD)</div>
                        Pengunjung memilih pembayaran tunai saat tiba di gerbang Kebun Raya. Tidak ada foto transfer.
                    @else
                        Pengunjung belum mengunggah foto bukti pembayaran.
                    @endif
                </div>
            @endif
        </div>

        <!-- Right: Verification Actions & Customer Details -->
        <div class="space-y-6">

            <!-- Admin Approval & Rejection Actions -->
            <div class="bg-white rounded-lg border border-slate-200 p-6 shadow-2xs space-y-4">
                <div class="border-b border-slate-100 pb-2">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-emerald-800">Tindakan Persetujuan Admin</h2>
                </div>

                @if($order->status === 'pending')
                    <div class="space-y-4">
                        <!-- LKPD Tahap 3: Admin menekan tombol Approve -> Status LUNAS -->
                        <form action="{{ route('admin.orders.approve', $order->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="admin_notes" value="Pembayaran sah dan telah diverifikasi oleh Admin.">
                            <button 
                                type="submit" 
                                class="w-full bg-emerald-800 hover:bg-emerald-900 text-white font-bold text-xs uppercase tracking-wider py-3 px-4 rounded transition shadow-xs cursor-pointer"
                            >
                                Konfirmasi & Setujui Pembayaran (Lunas)
                            </button>
                        </form>

                        <!-- Reject Form with Note -->
                        <form action="{{ route('admin.orders.reject', $order->id) }}" method="POST" class="space-y-2 pt-2 border-t border-slate-100">
                            @csrf
                            <label for="admin_notes" class="block text-[11px] font-bold uppercase tracking-wider text-rose-900">
                                Tolak Bukti Pembayaran (Wajib Isi Alasan)
                            </label>
                            <input 
                                type="text" 
                                id="admin_notes" 
                                name="admin_notes" 
                                placeholder="Contoh: Nominal transfer kurang atau bukti tidak terbaca jelas."
                                required
                                class="w-full px-3 py-1.5 text-xs bg-slate-50 border border-slate-300 rounded font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-800"
                            >
                            <button 
                                type="submit" 
                                class="w-full bg-rose-700 hover:bg-rose-800 text-white font-bold text-xs uppercase tracking-wider py-2 px-3 rounded transition cursor-pointer"
                            >
                                Tolak Bukti Pembayaran
                            </button>
                        </form>
                    </div>
                @elseif($order->status === 'confirmed')
                    <div class="space-y-3">
                        <div class="bg-emerald-50 border border-emerald-200 text-emerald-900 p-3 rounded text-xs">
                            <div class="font-bold">Status: LUNAS & TERVERIFIKASI</div>
                            Dikonfirmasi pada {{ $order->confirmed_at?->translatedFormat('d F Y, H:i') }} WIB. E-Ticket pengunjung telah aktif.
                        </div>

                        <form action="{{ route('admin.orders.markUsed', $order->id) }}" method="POST">
                            @csrf
                            <button 
                                type="submit" 
                                class="w-full bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs uppercase tracking-wider py-2.5 px-4 rounded transition cursor-pointer"
                            >
                                Tandai Digunakan (Check-In Pengunjung)
                            </button>
                        </form>
                    </div>
                @elseif($order->status === 'used')
                    <div class="bg-slate-100 border border-slate-300 text-slate-800 p-3 rounded text-xs space-y-1">
                        <div class="font-bold">TIKET TELAH DIGUNAKAN (CHECKED-IN)</div>
                        <div>Waktu Masuk: {{ $order->used_at?->translatedFormat('d F Y, H:i') }} WIB</div>
                    </div>
                @elseif($order->status === 'rejected')
                    <div class="bg-rose-50 border border-rose-200 text-rose-900 p-3 rounded text-xs space-y-1">
                        <div class="font-bold">STATUS: DITOLAK</div>
                        <div>Alasan: {{ $order->admin_notes }}</div>
                    </div>
                @endif
            </div>

            <!-- Customer & Visit Metadata -->
            <div class="bg-white rounded-lg border border-slate-200 p-6 shadow-2xs space-y-3 text-xs">
                <div class="border-b border-slate-100 pb-2">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-700">Data Pemesan</h2>
                </div>

                <div class="space-y-2">
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Nama Lengkap:</span>
                        <span class="font-bold text-slate-900">{{ $order->customer_name }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Email:</span>
                        <span class="font-semibold text-slate-900">{{ $order->customer_email }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">No. WhatsApp / Telp:</span>
                        <span class="font-semibold text-slate-900">{{ $order->customer_phone ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Tanggal Kunjungan:</span>
                        <span class="font-black text-emerald-950">{{ $order->visit_date->translatedFormat('d F Y') }}</span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-slate-500">Akun Terdaftar:</span>
                        <span class="font-semibold text-slate-700">{{ $order->user->name }} (ID: {{ $order->user_id }})</span>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- Items Breakdown Table -->
    <div class="bg-white rounded-lg border border-slate-200 p-6 shadow-2xs space-y-4">
        <div class="border-b border-slate-100 pb-2">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-700">Rincian Karcis Masuk</h2>
        </div>

        <table class="w-full text-left text-xs text-slate-700 border-collapse">
            <thead>
                <tr class="border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider text-[11px] bg-slate-50">
                    <th class="py-2.5 px-3">Kategori Tiket</th>
                    <th class="py-2.5 px-3 text-right">Tarif Satuan</th>
                    <th class="py-2.5 px-3 text-center">Kuantitas</th>
                    <th class="py-2.5 px-3 text-right">Subtotal</th>
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
                        Total Tagihan:
                    </td>
                    <td class="py-2.5 px-3 text-right text-emerald-950 font-black text-sm">
                        {{ $order->formatted_total }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Back Navigation -->
    <div class="pt-2">
        <a href="{{ route('admin.orders.index') }}" class="text-xs font-bold uppercase tracking-wider text-slate-600 hover:text-slate-900 transition">
            &larr; Kembali ke Daftar Seluruh Pesanan
        </a>
    </div>

</div>
@endsection
