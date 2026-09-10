@extends('layouts.admin')

@section('title', 'Dashboard Ringkasan Pengelola')

@section('content')
<div class="space-y-8">

    <!-- Dashboard Title Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 pb-4">
        <div>
            <div class="text-xs font-bold uppercase tracking-wider text-emerald-800">Panel Kendali Pengelola</div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">
                Ringkasan Penjualan & Operasional Tiket
            </h1>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.tickets.create') }}" class="bg-emerald-800 hover:bg-emerald-900 text-white text-xs font-bold uppercase tracking-wider px-4 py-2 rounded transition shadow-xs">
                + Tambah Produk Tiket
            </a>
            <a href="{{ route('admin.orders.index') }}" class="bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold uppercase tracking-wider px-4 py-2 rounded transition">
                Daftar Pesanan
            </a>
        </div>
    </div>

    <!-- 5 Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        
        <!-- Total Orders -->
        <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-2xs">
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Pesanan</div>
            <div class="text-2xl font-black text-slate-900 mt-1">{{ $metrics['total_orders'] }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Seluruh transaksi masuk</div>
        </div>

        <!-- Pending Orders -->
        <div class="bg-white p-5 rounded-lg border border-amber-200 shadow-2xs bg-amber-50/20">
            <div class="text-[11px] font-bold uppercase tracking-wider text-amber-800">Menunggu Konfirmasi</div>
            <div class="text-2xl font-black text-amber-900 mt-1">{{ $metrics['pending_orders'] }}</div>
            <div class="text-[11px] text-amber-700 mt-1">Perlu tindakan verifikasi</div>
        </div>

        <!-- Confirmed Orders -->
        <div class="bg-white p-5 rounded-lg border border-emerald-200 shadow-2xs bg-emerald-50/20">
            <div class="text-[11px] font-bold uppercase tracking-wider text-emerald-800">Lunas / Terverifikasi</div>
            <div class="text-2xl font-black text-emerald-950 mt-1">{{ $metrics['confirmed_orders'] }}</div>
            <div class="text-[11px] text-emerald-700 mt-1">E-Ticket aktif & digunakan</div>
        </div>

        <!-- Total Revenue -->
        <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-2xs">
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Pendapatan</div>
            <div class="text-xl font-black text-slate-900 mt-1">Rp {{ number_format($metrics['total_revenue'], 0, ',', '.') }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Dari tiket lunas</div>
        </div>

        <!-- Active Tickets -->
        <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-2xs">
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Produk Tiket Aktif</div>
            <div class="text-2xl font-black text-slate-900 mt-1">{{ $metrics['active_tickets'] }} <span class="text-xs font-normal text-slate-400">/ {{ $metrics['total_tickets_created'] }}</span></div>
            <div class="text-[11px] text-slate-400 mt-1">Kategori tiket publik</div>
        </div>

    </div>

    <!-- Recent Transactions Table -->
    <div class="bg-white rounded-lg border border-slate-200 shadow-2xs space-y-4 p-6">
        <div class="flex justify-between items-center border-b border-slate-100 pb-3">
            <div>
                <div class="text-xs font-bold uppercase tracking-wider text-emerald-800">Aktivitas Terkini</div>
                <h2 class="text-base font-bold text-slate-900">Pesanan Masuk Terbaru</h2>
            </div>
            <a href="{{ route('admin.orders.index') }}" class="text-xs font-bold text-emerald-800 hover:underline">
                Lihat Semua Pesanan &rarr;
            </a>
        </div>

        @if($recentOrders->isEmpty())
            <div class="text-center py-8 text-xs text-slate-500">
                Belum ada transaksi pemesanan tiket yang masuk.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-700 border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider text-[10px] bg-slate-50">
                            <th class="py-2.5 px-3">Invoice</th>
                            <th class="py-2.5 px-3">Nama Pemesan</th>
                            <th class="py-2.5 px-3">Tgl Kunjungan</th>
                            <th class="py-2.5 px-3 text-right">Total</th>
                            <th class="py-2.5 px-3 text-center">Metode</th>
                            <th class="py-2.5 px-3 text-center">Status</th>
                            <th class="py-2.5 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($recentOrders as $order)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-2.5 px-3 font-mono font-bold text-slate-900">
                                    {{ $order->invoice_number }}
                                </td>
                                <td class="py-2.5 px-3 font-medium text-slate-800">
                                    {{ $order->customer_name }}
                                </td>
                                <td class="py-2.5 px-3 text-slate-600">
                                    {{ $order->visit_date->translatedFormat('d M Y') }}
                                </td>
                                <td class="py-2.5 px-3 text-right font-bold text-slate-900">
                                    {{ $order->formatted_total }}
                                </td>
                                <td class="py-2.5 px-3 text-center uppercase font-semibold text-[10px]">
                                    {{ $order->payment_method }}
                                </td>
                                <td class="py-2.5 px-3 text-center">
                                    @if($order->status === 'confirmed')
                                        <span class="inline-block text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-emerald-100 text-emerald-900">Lunas</span>
                                    @elseif($order->status === 'used')
                                        <span class="inline-block text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-slate-100 text-slate-700">Check-in</span>
                                    @elseif($order->status === 'rejected')
                                        <span class="inline-block text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-rose-100 text-rose-900">Ditolak</span>
                                    @else
                                        <span class="inline-block text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-amber-100 text-amber-900">Pending</span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3 text-right whitespace-nowrap space-x-1">
                                    <a href="{{ route('admin.orders.show', $order->id) }}" class="inline-block bg-slate-100 hover:bg-slate-200 text-slate-800 text-[11px] font-bold px-2 py-1 rounded">
                                        Periksa
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>
@endsection
