@extends('layouts.app')

@section('title', 'Riwayat Transaksi Pemesanan Tiket')

@section('content')
<div class="space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 pb-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">
                Riwayat Transaksi Tiket
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Daftar seluruh invoice dan tiket kunjungan Kebun Raya atas akun Anda.
            </p>
        </div>

        <a href="{{ route('home') }}" class="inline-flex items-center bg-emerald-800 hover:bg-emerald-900 text-white text-xs font-bold uppercase tracking-wider px-4 py-2.5 rounded transition self-start sm:self-auto">
            + Pesan Tiket Baru
        </a>
    </div>

    <!-- Orders Table or Empty State -->
    @if($orders->isEmpty())
        <div class="bg-white rounded-lg border border-slate-200 p-12 text-center space-y-4">
            <div class="max-w-md mx-auto">
                <div class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-1">Belum Ada Riwayat Pemesanan</div>
                <p class="text-xs text-slate-500 leading-relaxed mb-6">
                    Anda belum memiliki transaksi tiket aktif. Silakan pilih tanggal kunjungan dan kategori tiket yang Anda butuhkan melalui formulir pemesanan.
                </p>
                <a href="{{ route('home') }}" class="bg-emerald-800 hover:bg-emerald-900 text-white text-xs font-bold uppercase tracking-wider px-5 py-2.5 rounded transition">
                    Mulai Pemesanan Tiket Sekarang
                </a>
            </div>
        </div>
    @else
        <div class="bg-white rounded-lg border border-slate-200 shadow-2xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-700 border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider text-[11px] bg-slate-50">
                            <th class="py-3 px-4">No. Invoice</th>
                            <th class="py-3 px-4">Tanggal Kunjungan</th>
                            <th class="py-3 px-4">Rincian Tiket</th>
                            <th class="py-3 px-4 text-right">Total Tagihan</th>
                            <th class="py-3 px-4 text-center">Metode</th>
                            <th class="py-3 px-4 text-center">Status Pembayaran</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($orders as $order)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3 px-4 font-mono font-bold text-slate-900">
                                    <a href="{{ route('orders.show', $order->invoice_number) }}" class="text-emerald-900 hover:underline">
                                        {{ $order->invoice_number }}
                                    </a>
                                </td>
                                <td class="py-3 px-4 text-slate-700 font-medium">
                                    {{ $order->visit_date->translatedFormat('d M Y') }}
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-semibold text-slate-800">{{ $order->total_tickets }} Tiket</div>
                                    <div class="text-[11px] text-slate-500">
                                        {{ $order->items->pluck('ticket_name')->implode(', ') }}
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-right font-bold text-slate-900">
                                    {{ $order->formatted_total }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-slate-100 text-slate-700">
                                        {{ $order->payment_method === 'transfer' ? 'Transfer' : 'COD Loket' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if($order->status === 'confirmed')
                                        <span class="inline-block text-[10px] font-bold uppercase tracking-wider px-2.5 py-0.5 rounded bg-emerald-100 text-emerald-900 border border-emerald-300">
                                            LUNAS / TERVERIFIKASI
                                        </span>
                                    @elseif($order->status === 'used')
                                        <span class="inline-block text-[10px] font-bold uppercase tracking-wider px-2.5 py-0.5 rounded bg-slate-100 text-slate-700 border border-slate-300">
                                            SUDAH DIGUNAKAN
                                        </span>
                                    @elseif($order->status === 'rejected')
                                        <span class="inline-block text-[10px] font-bold uppercase tracking-wider px-2.5 py-0.5 rounded bg-rose-100 text-rose-900 border border-rose-300">
                                            DITOLAK
                                        </span>
                                    @else
                                        <span class="inline-block text-[10px] font-bold uppercase tracking-wider px-2.5 py-0.5 rounded bg-amber-100 text-amber-900 border border-amber-300">
                                            MENUNGGU KONFIRMASI
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right whitespace-nowrap space-x-2">
                                    <a href="{{ route('orders.show', $order->invoice_number) }}" class="text-xs font-semibold text-slate-700 hover:text-emerald-900 underline">
                                        Detail
                                    </a>

                                    @if($order->canDownloadTicket())
                                        <a href="{{ route('orders.eticket', $order->invoice_number) }}" class="inline-block bg-emerald-800 hover:bg-emerald-900 text-white text-[11px] font-bold uppercase tracking-wider px-2.5 py-1 rounded transition">
                                            E-Ticket
                                        </a>
                                    @else
                                        <span class="inline-block bg-slate-100 text-slate-400 text-[11px] font-bold uppercase tracking-wider px-2.5 py-1 rounded border border-slate-200 cursor-not-allowed select-none" title="E-Ticket terkunci hingga pembayaran LUNAS">
                                            Terkunci
                                        </span>
                                    @endif
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
