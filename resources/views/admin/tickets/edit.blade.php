@extends('layouts.admin')

@section('title', 'Edit Produk Tiket - ' . $ticket->name)

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <div class="border-b border-slate-200 pb-4">
        <div class="text-xs font-bold uppercase tracking-wider text-emerald-800">Manajemen Inventaris</div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">
            Perbarui Produk Tiket: {{ $ticket->name }}
        </h1>
    </div>

    <form action="{{ route('admin.tickets.update', $ticket->id) }}" method="POST" class="bg-white rounded-lg border border-slate-200 p-6 shadow-2xs space-y-5">
        @csrf
        @method('PUT')

        <div>
            <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                Nama Produk Tiket *
            </label>
            <input 
                type="text" 
                id="name" 
                name="name" 
                value="{{ old('name', $ticket->name) }}" 
                required 
                class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded font-medium text-slate-800 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-800"
            >
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="category" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                    Kategori Pengunjung *
                </label>
                <select 
                    id="category" 
                    name="category" 
                    required 
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded font-medium text-slate-800 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-800"
                >
                    @foreach([
                        'Wisatawan Domestik (Dewasa)',
                        'Wisatawan Domestik (Anak)',
                        'Wisatawan Mancanegara',
                        'Kendaraan / Wahana',
                        'Rombongan Edukasi'
                    ] as $cat)
                        <option value="{{ $cat }}" {{ old('category', $ticket->category) === $cat ? 'selected' : '' }}>
                            {{ $cat }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="price" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                    Tarif / Harga (Rupiah) *
                </label>
                <input 
                    type="number" 
                    id="price" 
                    name="price" 
                    value="{{ old('price', $ticket->price) }}" 
                    min="0" 
                    step="500"
                    required 
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded font-medium text-slate-800 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-800"
                >
            </div>
        </div>

        <div>
            <label for="quota" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                Kuota Kunjungan Harian *
            </label>
            <input 
                type="number" 
                id="quota" 
                name="quota" 
                value="{{ old('quota', $ticket->quota) }}" 
                min="1" 
                required 
                class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded font-medium text-slate-800 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-800"
            >
        </div>

        <div>
            <label for="description" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                Deskripsi / Informasi Tiket
            </label>
            <textarea 
                id="description" 
                name="description" 
                rows="3" 
                class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded font-medium text-slate-800 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-800"
            >{{ old('description', $ticket->description) }}</textarea>
        </div>

        <div>
            <label for="terms" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                Syarat & Ketentuan Tambahan
            </label>
            <textarea 
                id="terms" 
                name="terms" 
                rows="2" 
                class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded font-medium text-slate-800 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-800"
            >{{ old('terms', $ticket->terms) }}</textarea>
        </div>

        <div class="pt-1">
            <label class="flex items-center gap-2 cursor-pointer select-none">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $ticket->is_active) ? 'checked' : '' }} class="text-emerald-800 focus:ring-emerald-800 rounded">
                <span class="text-xs font-bold text-slate-800">Tiket aktif & tayang di portal publik</span>
            </label>
        </div>

        <div class="flex items-center justify-between pt-4 border-t border-slate-200">
            <a href="{{ route('admin.tickets.index') }}" class="text-xs font-bold uppercase tracking-wider text-slate-600 hover:text-slate-900 transition">
                &larr; Batal & Kembali
            </a>

            <button 
                type="submit" 
                class="bg-emerald-800 hover:bg-emerald-900 text-white font-bold text-xs uppercase tracking-wider py-2.5 px-6 rounded transition shadow-xs cursor-pointer"
            >
                Simpan Perubahan
            </button>
        </div>

    </form>

</div>
@endsection
