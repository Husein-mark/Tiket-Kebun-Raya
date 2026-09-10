@extends('layouts.admin')

@section('title', 'Tambah Produk Tiket Baru')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <div class="border-b border-slate-200 pb-4">
        <div class="text-xs font-bold uppercase tracking-wider text-emerald-800">Manajemen Inventaris</div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">
            Tambah Produk Tiket Baru
        </h1>
        <p class="text-xs text-slate-500 mt-1">
            Produk tiket yang dibuat di sini akan langsung muncul pada portal pemesanan pengunjung.
        </p>
    </div>

    <form action="{{ route('admin.tickets.store') }}" method="POST" class="bg-white rounded-lg border border-slate-200 p-6 shadow-2xs space-y-5">
        @csrf

        <div>
            <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                Nama Produk Tiket *
            </label>
            <input 
                type="text" 
                id="name" 
                name="name" 
                value="{{ old('name') }}" 
                placeholder="Contoh: Tiket Masuk Domestik Dewasa"
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
                    <option value="Wisatawan Domestik (Dewasa)">Wisatawan Domestik (Dewasa)</option>
                    <option value="Wisatawan Domestik (Anak)">Wisatawan Domestik (Anak)</option>
                    <option value="Wisatawan Mancanegara">Wisatawan Mancanegara</option>
                    <option value="Kendaraan / Wahana">Kendaraan / Wahana</option>
                    <option value="Rombongan Edukasi">Rombongan Edukasi</option>
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
                    value="{{ old('price', 25000) }}" 
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
                value="{{ old('quota', 1000) }}" 
                min="1" 
                required 
                class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded font-medium text-slate-800 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-800"
            >
            <p class="text-[11px] text-slate-500 mt-1">
                Batas maksimal pengunjung per hari untuk kategori tiket ini.
            </p>
        </div>

        <div>
            <label for="description" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                Deskripsi / Informasi Tiket
            </label>
            <textarea 
                id="description" 
                name="description" 
                rows="3" 
                placeholder="Contoh: Tiket perorangan untuk warga negara Indonesia usia 10 tahun ke atas. Akses ke seluruh taman koleksi botani."
                class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded font-medium text-slate-800 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-800"
            >{{ old('description') }}</textarea>
        </div>

        <div>
            <label for="terms" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                Syarat & Ketentuan Tambahan
            </label>
            <textarea 
                id="terms" 
                name="terms" 
                rows="2" 
                placeholder="Contoh: Wajib menunjukkan kartu identitas (KTP/Pelajar) saat pemindaian tiket di pintu gerbang."
                class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded font-medium text-slate-800 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-800"
            >{{ old('terms') }}</textarea>
        </div>

        <div class="pt-1">
            <label class="flex items-center gap-2 cursor-pointer select-none">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="text-emerald-800 focus:ring-emerald-800 rounded">
                <span class="text-xs font-bold text-slate-800">Publikasikan tiket ini langsung agar dapat dibeli pengunjung</span>
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
                Simpan & Publikasikan Tiket
            </button>
        </div>

    </form>

</div>
@endsection
