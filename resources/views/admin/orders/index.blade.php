@extends('layouts.admin')

@section('title', 'Verifikasi & Pesanan Tiket')

@section('content')
<div class="space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 pb-4">
        <div>
            <div class="text-xs font-bold uppercase tracking-wider text-emerald-800">Tahap 3 LKPD</div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">
                Verifikasi Pembayaran & Riwayat Pesanan
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Admin mengecek bukti bayar lalu menekan tombol Konfirmasi untuk mengubah status menjadi Lunas.
            </p>
        </div>
    </div>

    <!-- Status Filter Tabs -->
    <div class="flex flex-wrap items-center gap-2 border-b border-slate-200 pb-2">
        <a href="{{ route('admin.orders.index') }}" class="text-xs font-bold px-3 py-1.5 rounded transition {{ empty($status) ? 'bg-slate-900 text-white' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200' }}">
            Semua ({{ $counts['all'] }})
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="text-xs font-bold px-3 py-1.5 rounded transition {{ $status === 'pending' ? 'bg-amber-800 text-white' : 'bg-white text-amber-900 hover:bg-amber-50 border border-amber-200' }}">
            Menunggu Konfirmasi ({{ $counts['pending'] }})
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'confirmed']) }}" class="text-xs font-bold px-3 py-1.5 rounded transition {{ $status === 'confirmed' ? 'bg-emerald-800 text-white' : 'bg-white text-emerald-900 hover:bg-emerald-50 border border-emerald-200' }}">
            Lunas / Terverifikasi ({{ $counts['confirmed'] }})
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'used']) }}" class="text-xs font-bold px-3 py-1.5 rounded transition {{ $status === 'used' ? 'bg-slate-800 text-white' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200' }}">
            Sudah Digunakan ({{ $counts['used'] }})
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'rejected']) }}" class="text-xs font-bold px-3 py-1.5 rounded transition {{ $status === 'rejected' ? 'bg-rose-800 text-white' : 'bg-white text-rose-900 hover:bg-rose-50 border border-rose-200' }}">
            Ditolak ({{ $counts['rejected'] }})
        </a>
    </div>

    <!-- Orders Table -->
    @if($orders->isEmpty())
        <div class="bg-white rounded-lg border border-slate-200 p-12 text-center text-xs text-slate-500">
            Tidak ada transaksi pesanan yang sesuai dengan filter status yang dipilih.
        </div>
    @else
        <div class="bg-white rounded-lg border border-slate-200 shadow-2xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-700 border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider text-[11px] bg-slate-50">
                            <th class="py-3 px-4">Invoice</th>
                            <th class="py-3 px-4">Pemesan</th>
                            <th class="py-3 px-4">Kunjungan</th>
                            <th class="py-3 px-4 text-right">Tagihan</th>
                            <th class="py-3 px-4 text-center">Metode</th>
                            <th class="py-3 px-4 text-center">Bukti Bayar</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Tindakan Admin</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($orders as $order)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3 px-4 font-mono font-bold text-slate-900">
                                    <a href="{{ route('admin.orders.show', $order->id) }}" class="text-emerald-900 hover:underline">
                                        {{ $order->invoice_number }}
                                    </a>
                                </td>

                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-900">{{ $order->customer_name }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $order->customer_email }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $order->customer_phone ?? '-' }}</div>
                                </td>

                                <td class="py-3 px-4">
                                    <div class="font-semibold text-slate-800">{{ $order->visit_date->translatedFormat('d M Y') }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $order->total_tickets }} Tiket</div>
                                </td>

                                <td class="py-3 px-4 text-right font-black text-slate-900 text-sm">
                                    {{ $order->formatted_total }}
                                </td>

                                <td class="py-3 px-4 text-center">
                                    <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-slate-100 text-slate-700">
                                        {{ $order->payment_method === 'transfer' ? 'Transfer' : 'COD Loket' }}
                                    </span>
                                </td>

                                <!-- Payment Proof Preview -->
                                <td class="py-3 px-4 text-center">
                                    @if($order->payment_proof)
                                        <a href="{{ asset('storage/' . $order->payment_proof) }}" target="_blank" class="inline-block text-[11px] font-bold text-emerald-800 underline hover:text-emerald-950">
                                            Lihat Foto
                                        </a>
                                    @else
                                        <span class="text-[10px] text-slate-400 italic">
                                            {{ $order->payment_method === 'cod' ? 'Tunai Loket' : 'Tidak Ada' }}
                                        </span>
                                    @endif
                                </td>

                                <!-- Status Badge -->
                                <td class="py-3 px-4 text-center">
                                    @if($order->status === 'confirmed')
                                        <span class="inline-block text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-emerald-100 text-emerald-900 border border-emerald-300">
                                            LUNAS
                                        </span>
                                    @elseif($order->status === 'used')
                                        <span class="inline-block text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-slate-100 text-slate-700 border border-slate-300">
                                            DIGUNAKAN
                                        </span>
                                    @elseif($order->status === 'rejected')
                                        <span class="inline-block text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-rose-100 text-rose-900 border border-rose-300">
                                            DITOLAK
                                        </span>
                                    @else
                                        <span class="inline-block text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-amber-100 text-amber-900 border border-amber-300">
                                            PENDING
                                        </span>
                                    @endif
                                </td>

                                <!-- Admin Actions (LKPD Checklist #4: Admin menekan tombol Konfirmasi -> Status berubah Lunas) -->
                                <td class="py-3 px-4 text-right whitespace-nowrap space-x-1.5">
                                    @if($order->status === 'pending')
                                        <form action="{{ route('admin.orders.approve', $order->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="bg-emerald-800 hover:bg-emerald-900 text-white text-[11px] font-bold uppercase tracking-wider px-2.5 py-1 rounded transition cursor-pointer shadow-2xs">
                                                Konfirmasi
                                            </button>
                                        </form>
                                    @elseif($order->status === 'confirmed')
                                        <form action="{{ route('admin.orders.markUsed', $order->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="bg-slate-700 hover:bg-slate-800 text-white text-[11px] font-bold uppercase tracking-wider px-2 py-1 rounded transition cursor-pointer">
                                                Check-In
                                            </button>
                                        </form>
                                    @endif

                                    <a href="{{ route('admin.orders.show', $order->id) }}" class="text-xs font-semibold text-slate-700 hover:text-slate-900 underline">
                                        Rincian
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($orders->hasPages())
                <div class="p-4 border-t border-slate-200 bg-slate-50">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    @endif

</div>
@endsection
