@extends('layouts.app')

@section('title', 'Konfirmasi Pembayaran & Pemesanan')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">

    <!-- Stepper / Breadcrumb Header -->
    <div class="border-b border-slate-200 pb-4">
        <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-emerald-800 mb-1">
            <span>Pemesanan Tiket</span>
            <span>&rarr;</span>
            <span class="text-slate-900">Tahap 2: Pembayaran & Konfirmasi</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
            Konfirmasi Pesanan & Metode Pembayaran
        </h1>
        <p class="text-xs text-slate-500 mt-1">
            Periksa kembali rincian pemesanan Anda dan pilih metode pembayaran resmi.
        </p>
    </div>

    <form action="{{ route('checkout.process') }}" method="POST" enctype="multipart/form-data" id="checkoutForm" class="space-y-8">
        @csrf

        <!-- Section 1: Customer Information -->
        <div class="bg-white rounded-lg border border-slate-200 p-6 shadow-2xs space-y-4">
            <div class="border-b border-slate-100 pb-3">
                <div class="text-xs font-bold uppercase tracking-wider text-emerald-800">Identitas Pemesan</div>
                <h2 class="text-base font-bold text-slate-900">Data Pengunjung Utama</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="customer_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                        Nama Lengkap
                    </label>
                    <input 
                        type="text" 
                        id="customer_name" 
                        name="customer_name" 
                        value="{{ old('customer_name', $user->name) }}" 
                        required
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded font-medium text-slate-800 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-800"
                    >
                </div>

                <div>
                    <label for="customer_email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                        Alamat Email
                    </label>
                    <input 
                        type="email" 
                        id="customer_email" 
                        name="customer_email" 
                        value="{{ old('customer_email', $user->email) }}" 
                        required
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded font-medium text-slate-800 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-800"
                    >
                </div>

                <div>
                    <label for="customer_phone" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                        Nomor HP / WhatsApp
                    </label>
                    <input 
                        type="tel" 
                        id="customer_phone" 
                        name="customer_phone" 
                        value="{{ old('customer_phone', $user->phone ?? '08') }}" 
                        required
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded font-medium text-slate-800 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-800"
                    >
                </div>
            </div>
            <p class="text-[11px] text-slate-500">
                E-Ticket dan bukti konfirmasi resmi akan dikaitkan dengan akun dan alamat email di atas.
            </p>
        </div>

        <!-- Section 2: Order Items Summary Table -->
        <div class="bg-white rounded-lg border border-slate-200 p-6 shadow-2xs space-y-4">
            <div class="border-b border-slate-100 pb-3 flex justify-between items-center">
                <div>
                    <div class="text-xs font-bold uppercase tracking-wider text-emerald-800">Rincian Tiket</div>
                    <h2 class="text-base font-bold text-slate-900">Tiket yang Dipesan</h2>
                </div>
                <div class="text-right">
                    <span class="text-xs text-slate-500">Tanggal Kunjungan:</span>
                    <span class="text-xs font-bold text-slate-900 ml-1">
                        {{ \Carbon\Carbon::parse($draft['visit_date'])->translatedFormat('d F Y') }}
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-700 border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider text-[11px] bg-slate-50">
                            <th class="py-2.5 px-3">Jenis Tiket</th>
                            <th class="py-2.5 px-3 text-right">Harga Satuan</th>
                            <th class="py-2.5 px-3 text-center">Jumlah</th>
                            <th class="py-2.5 px-3 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($draft['items'] as $item)
                            <tr>
                                <td class="py-3 px-3 font-semibold text-slate-900">
                                    {{ $item['name'] }}
                                </td>
                                <td class="py-3 px-3 text-right">
                                    Rp {{ number_format($item['price'], 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-3 text-center font-bold">
                                    {{ $item['quantity'] }}
                                </td>
                                <td class="py-3 px-3 text-right font-bold text-slate-900">
                                    Rp {{ number_format($item['subtotal'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-slate-300 bg-slate-50">
                            <td colspan="3" class="py-3 px-3 text-right font-bold text-slate-900 uppercase tracking-wider text-xs">
                                Total Tagihan Pembayaran:
                            </td>
                            <td class="py-3 px-3 text-right font-black text-emerald-950 text-base">
                                Rp {{ number_format($draft['total_amount'], 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Section 3: Payment Method Selection -->
        <div class="bg-white rounded-lg border border-slate-200 p-6 shadow-2xs space-y-6">
            <div class="border-b border-slate-100 pb-3">
                <div class="text-xs font-bold uppercase tracking-wider text-emerald-800">Tahap 2 LKPD</div>
                <h2 class="text-base font-bold text-slate-900">Pilih Metode Pembayaran</h2>
            </div>

            <!-- Two Payment Method Radio Options -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                
                <!-- Option 1: Transfer Bank -->
                <label class="payment-card border-2 rounded-lg p-5 cursor-pointer transition flex flex-col justify-between {{ old('payment_method', 'transfer') === 'transfer' ? 'border-emerald-800 bg-emerald-50/40' : 'border-slate-200 bg-white hover:border-slate-300' }}">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-emerald-900 block mb-1">Opsi 1</span>
                            <div class="font-bold text-slate-900 text-sm">Transfer Bank</div>
                            <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                                Bayar via transfer online (BCA / Mandiri) dan unggah foto bukti transfer.
                            </p>
                        </div>
                        <input 
                            type="radio" 
                            name="payment_method" 
                            value="transfer" 
                            {{ old('payment_method', 'transfer') === 'transfer' ? 'checked' : '' }}
                            class="text-emerald-800 focus:ring-emerald-800 mt-1"
                        >
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-200/70 text-[11px] font-semibold text-emerald-900">
                        Memerlukan Unggah Bukti Bayar
                    </div>
                </label>

                <!-- Option 2: COD (Bayar di Tempat) -->
                <label class="payment-card border-2 rounded-lg p-5 cursor-pointer transition flex flex-col justify-between {{ old('payment_method') === 'cod' ? 'border-emerald-800 bg-emerald-50/40' : 'border-slate-200 bg-white hover:border-slate-300' }}">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-600 block mb-1">Opsi 2</span>
                            <div class="font-bold text-slate-900 text-sm">COD / Bayar di Tempat</div>
                            <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                                Bayar tunai secara langsung di loket Kebun Raya saat hari kunjungan.
                            </p>
                        </div>
                        <input 
                            type="radio" 
                            name="payment_method" 
                            value="cod" 
                            {{ old('payment_method') === 'cod' ? 'checked' : '' }}
                            class="text-emerald-800 focus:ring-emerald-800 mt-1"
                        >
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-200/70 text-[11px] font-semibold text-slate-700">
                        Bayar Tunai di Loket Gerbang
                    </div>
                </label>

            </div>

            <!-- Details for Transfer Bank -->
            <div id="transferDetailsBox" class="space-y-4 pt-2 {{ old('payment_method', 'transfer') === 'transfer' ? '' : 'hidden' }}">
                <div class="bg-slate-50 border border-slate-200 rounded-lg p-5 space-y-4">
                    <div class="font-bold text-xs uppercase tracking-wider text-slate-700">
                        Rekening Resmi Pembayaran Tiket Kebun Raya
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="bg-white p-3.5 rounded border border-slate-200">
                            <div class="text-[11px] font-bold text-slate-500 uppercase">Bank Mandiri</div>
                            <div class="text-base font-black text-slate-900 tracking-wider my-0.5">133-00-9876543-2</div>
                            <div class="text-[11px] text-slate-600">a.n. PT Kebun Raya Indonesia</div>
                        </div>

                        <div class="bg-white p-3.5 rounded border border-slate-200">
                            <div class="text-[11px] font-bold text-slate-500 uppercase">Bank Central Asia (BCA)</div>
                            <div class="text-base font-black text-slate-900 tracking-wider my-0.5">869-0123456</div>
                            <div class="text-[11px] text-slate-600">a.n. PT Kebun Raya Indonesia</div>
                        </div>
                    </div>

                    <div class="text-xs text-slate-600 leading-relaxed">
                        Total yang harus ditransfer tepat: <strong class="text-emerald-950 font-bold">Rp {{ number_format($draft['total_amount'], 0, ',', '.') }}</strong>. Setelah melakukan transfer, silakan kirim foto struk / tangkapan layar bukti transfer melalui tombol di bawah.
                    </div>
                </div>

                <!-- Receipt Upload Section (Tombol Tambahan Foto Bukti Transfer) -->
                <div class="border border-emerald-900/30 bg-emerald-50/50 p-5 rounded-lg space-y-3">
                    <label for="payment_proof" class="block text-xs font-bold uppercase tracking-wider text-emerald-950">
                        Tombol Tambahan: Kirimkan Bukti Foto Transfer
                    </label>
                    
                    <input 
                        type="file" 
                        id="payment_proof" 
                        name="payment_proof" 
                        accept="image/*"
                        class="block w-full text-xs text-slate-600 file:mr-4 file:py-2.5 file:px-4 file:rounded file:border-0 file:text-xs file:font-bold file:uppercase file:tracking-wider file:bg-emerald-800 file:text-white hover:file:bg-emerald-900 file:cursor-pointer border border-slate-300 rounded p-1 bg-white"
                    >
                    <p class="text-[11px] text-slate-600">
                        Format yang didukung: JPG, PNG, WEBP. Maksimal ukuran file 2 MB. Bukti akan diverifikasi oleh Admin.
                    </p>
                </div>
            </div>

            <!-- Details for COD -->
            <div id="codDetailsBox" class="space-y-4 pt-2 {{ old('payment_method') === 'cod' ? '' : 'hidden' }}">
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-5 text-amber-900 space-y-2">
                    <div class="font-bold text-xs uppercase tracking-wider text-amber-950">
                        Petunjuk Pembayaran di Tempat (COD)
                    </div>
                    <p class="text-xs leading-relaxed">
                        Anda telah memilih pembayaran tunai langsung di loket Kebun Raya. Simpan kode invoice pemesanan yang akan muncul setelah tombol submit ditekan, lalu tunjukkan kode invoice tersebut kepada petugas loket untuk pelunasan saat tiba di Kebun Raya.
                    </p>
                </div>
            </div>

        </div>

        <!-- Action Button -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4">
            <a href="{{ route('home') }}" class="text-xs font-bold uppercase tracking-wider text-slate-600 hover:text-slate-900 transition">
                &larr; Ubah Pilihan Tiket
            </a>

            <button 
                type="submit" 
                class="w-full sm:w-auto bg-emerald-800 hover:bg-emerald-900 text-white font-bold text-sm uppercase tracking-wider py-3.5 px-8 rounded transition shadow-xs"
            >
                Konfirmasi & Buat Pesanan
            </button>
        </div>

    </form>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const radioInputs = document.querySelectorAll('input[name="payment_method"]');
    const transferBox = document.getElementById('transferDetailsBox');
    const codBox = document.getElementById('codDetailsBox');
    const proofInput = document.getElementById('payment_proof');
    const cards = document.querySelectorAll('.payment-card');

    function updatePaymentMethod() {
        let selectedMethod = 'transfer';
        radioInputs.forEach(radio => {
            if (radio.checked) {
                selectedMethod = radio.value;
            }
        });

        cards.forEach(card => {
            const radio = card.querySelector('input[name="payment_method"]');
            if (radio.checked) {
                card.classList.add('border-emerald-800', 'bg-emerald-50/40');
                card.classList.remove('border-slate-200', 'bg-white');
            } else {
                card.classList.remove('border-emerald-800', 'bg-emerald-50/40');
                card.classList.add('border-slate-200', 'bg-white');
            }
        });

        if (selectedMethod === 'transfer') {
            transferBox.classList.remove('hidden');
            codBox.classList.add('hidden');
            proofInput.setAttribute('required', 'required');
        } else {
            transferBox.classList.add('hidden');
            codBox.classList.remove('hidden');
            proofInput.removeAttribute('required');
        }
    }

    radioInputs.forEach(radio => {
        radio.addEventListener('change', updatePaymentMethod);
    });

    updatePaymentMethod();
});
</script>
@endpush
@endsection
