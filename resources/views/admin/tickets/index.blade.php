@extends('layouts.admin')

@section('title', 'Kelola Produk Tiket')

@section('content')
<div class="space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 pb-4">
        <div>
            <div class="text-xs font-bold uppercase tracking-wider text-emerald-800">Manajemen Inventaris</div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">
                Daftar Produk Tiket Masuk Kebun Raya
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Produk tiket dibuat dan dikelola secara mandiri oleh Admin pengelola.
            </p>
        </div>

        <a href="{{ route('admin.tickets.create') }}" class="inline-flex items-center bg-emerald-800 hover:bg-emerald-900 text-white text-xs font-bold uppercase tracking-wider px-4 py-2.5 rounded transition shadow-xs">
            + Tambah Produk Tiket Baru
        </a>
    </div>

    <!-- Tickets Table or Empty State -->
    @if($tickets->isEmpty())
        <div class="bg-white rounded-lg border border-slate-200 p-12 text-center space-y-4">
            <div class="max-w-md mx-auto">
                <div class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-1">Belum Ada Produk Tiket</div>
                <p class="text-xs text-slate-500 leading-relaxed mb-6">
                    Sesuai ketentuan, produk tiket tidak digenerate melalui seeder melainkan dibuat langsung oleh Anda sebagai Admin. Silakan buat tiket perdana sekarang.
                </p>
                <a href="{{ route('admin.tickets.create') }}" class="bg-emerald-800 hover:bg-emerald-900 text-white text-xs font-bold uppercase tracking-wider px-5 py-2.5 rounded transition">
                    + Buat Produk Tiket Pertama
                </a>
            </div>
        </div>
    @else
        <div class="bg-white rounded-lg border border-slate-200 shadow-2xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-700 border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider text-[11px] bg-slate-50">
                            <th class="py-3 px-4">Nama Produk Tiket</th>
                            <th class="py-3 px-4">Kategori</th>
                            <th class="py-3 px-4 text-right">Harga Tiket</th>
                            <th class="py-3 px-4 text-center">Kuota Harian</th>
                            <th class="py-3 px-4 text-center">Status Publikasi</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($tickets as $ticket)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-900 text-sm">{{ $ticket->name }}</div>
                                    @if($ticket->description)
                                        <div class="text-[11px] text-slate-500 max-w-sm truncate">{{ $ticket->description }}</div>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-slate-100 text-slate-800 border border-slate-200">
                                        {{ $ticket->category }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right font-black text-slate-900 text-sm">
                                    {{ $ticket->formatted_price }}
                                </td>
                                <td class="py-3 px-4 text-center font-bold text-slate-700">
                                    {{ $ticket->quota }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if($ticket->is_active)
                                        <span class="inline-block text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-emerald-100 text-emerald-900 border border-emerald-300">
                                            Aktif / Tayang
                                        </span>
                                    @else
                                        <span class="inline-block text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-slate-100 text-slate-600 border border-slate-300">
                                            Non-Aktif
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right whitespace-nowrap space-x-2">
                                    <!-- Toggle Status Form -->
                                    <form action="{{ route('admin.tickets.toggle', $ticket->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-xs font-semibold text-slate-600 hover:text-slate-900 underline cursor-pointer">
                                            {{ $ticket->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>

                                    <!-- Edit Link -->
                                    <a href="{{ route('admin.tickets.edit', $ticket->id) }}" class="text-xs font-semibold text-emerald-800 hover:text-emerald-900 underline">
                                        Edit
                                    </a>

                                    <!-- Delete Form -->
                                    <form action="{{ route('admin.tickets.destroy', $ticket->id) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus tiket ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-semibold text-rose-700 hover:text-rose-900 underline cursor-pointer">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($tickets->hasPages())
                <div class="p-4 border-t border-slate-200 bg-slate-50">
                    {{ $tickets->links() }}
                </div>
            @endif
        </div>
    @endif

</div>
@endsection
