@extends('layouts.app')

@section('title', 'Pemesanan Tiket Online')

@section('content')
<div class="space-y-8">

    <!-- Hero Introduction Header -->
    <div class="bg-gradient-to-r from-emerald-950 via-emerald-900 to-emerald-950 text-white rounded-lg p-8 sm:p-10 border border-emerald-900 shadow-sm">
        <div class="max-w-3xl">
            <span class="inline-block text-xs uppercase tracking-widest font-bold text-emerald-300 bg-emerald-900/60 px-3 py-1 rounded border border-emerald-700/50 mb-3">
                Sistem E-Ticketing Mandiri
            </span>
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight mb-3">
                Pemesanan Tiket Masuk Kebun Raya
            </h1>
            <p class="text-sm sm:text-base text-emerald-100 leading-relaxed">
                Nikmati keasrian dan keanekaragaman koleksi flora nusantara. Rencanakan kunjungan edukatif dan rekreasi keluarga Anda secara praktis melalui portal pemesanan tiket resmi.
            </p>
        </div>
    </div>

    <!-- Booking Form Container -->
    <form action="{{ route('booking.draft') }}" method="POST" id="bookingForm" class="space-y-8">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

            <!-- Left & Middle: Date and Ticket Selection (2 Columns) -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Step 1: Visit Date Selection -->
                <div class="bg-white rounded-lg border border-slate-200 p-6 shadow-2xs">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
                        <div>
                            <div class="text-xs font-bold uppercase tracking-wider text-emerald-800">Tahap 1</div>
                            <h2 class="text-lg font-bold text-slate-900">Pilih Tanggal Kunjungan</h2>
                        </div>
                        <span class="text-xs font-medium text-slate-500">Wajib Diisi</span>
                    </div>

                    <div class="max-w-md">
                        <label for="visit_date" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                            Tanggal Kehadiran di Lokasi
                        </label>
                        <input 
                            type="date" 
                            id="visit_date" 
                            name="visit_date" 
                            min="{{ date('Y-m-d') }}" 
                            value="{{ old('visit_date', $draft['visit_date'] ?? date('Y-m-d')) }}" 
                            required
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-800 focus:border-emerald-800 transition text-sm"
                        >
                        <p class="text-xs text-slate-500 mt-2">
                            Tiket hanya berlaku pada tanggal kunjungan yang Anda tentukan di atas.
                        </p>
                    </div>
                </div>

                <!-- Step 2: Ticket Categories & Quantity -->
                <div class="bg-white rounded-lg border border-slate-200 p-6 shadow-2xs">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
                        <div>
                            <div class="text-xs font-bold uppercase tracking-wider text-emerald-800">Tahap 2</div>
                            <h2 class="text-lg font-bold text-slate-900">Pilih Kategori & Jumlah Tiket</h2>
                        </div>
                        <span class="text-xs font-medium text-slate-500">Harga Terhitung Otomatis</span>
                    </div>

                    @if($tickets->isEmpty())
                        <div class="bg-amber-50 border border-amber-200 text-amber-900 p-6 rounded text-center">
                            <div class="font-bold text-base mb-1">Kategori Tiket Sedang Dipersiapkan</div>
                            <p class="text-xs text-amber-800 max-w-lg mx-auto mb-4">
                                Saat ini pengelola Kebun Raya belum mempublikasikan produk tiket. Produk tiket dibuat secara mandiri oleh Admin melalui panel pengelola.
                            </p>
                            @auth
                                @if(Auth::user()->isAdmin())
                                    <a href="{{ route('admin.tickets.create') }}" class="inline-block bg-slate-900 text-white text-xs font-bold uppercase tracking-wider px-4 py-2 rounded hover:bg-slate-800 transition">
                                        Tambah Produk Tiket di Panel Admin
                                    </a>
                                @endif
                            @else
                                <a href="{{ route('login') }}" class="inline-block bg-emerald-800 text-white text-xs font-bold uppercase tracking-wider px-4 py-2 rounded hover:bg-emerald-900 transition">
                                    Masuk Sebagai Admin untuk Menambah Tiket
                                </a>
                            @endauth
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($tickets as $ticket)
                                @php
                                    $draftQty = $draft['items'][$ticket->id]['quantity'] ?? old("tickets.{$ticket->id}", 0);
                                @endphp
                                <div class="border border-slate-200 hover:border-emerald-700/60 rounded-lg p-5 transition bg-white" data-ticket-id="{{ $ticket->id }}" data-price="{{ $ticket->price }}">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 bg-emerald-100 text-emerald-900 rounded">
                                                    {{ $ticket->category }}
                                                </span>
                                                <span class="text-xs text-slate-500">
                                                    Kuota Harian: {{ $ticket->quota }} tiket
                                                </span>
                                            </div>
                                            <h3 class="font-bold text-base text-slate-900">{{ $ticket->name }}</h3>
                                            @if($ticket->description)
                                                <p class="text-xs text-slate-600 mt-1 leading-relaxed">{{ $ticket->description }}</p>
                                            @endif
                                            <div class="text-base font-extrabold text-emerald-900 mt-2">
                                                {{ $ticket->formatted_price }} <span class="text-xs font-normal text-slate-500">/ orang</span>
                                            </div>
                                        </div>

                                        <!-- Quantity Stepper without icons -->
                                        <div class="flex items-center gap-2 self-start sm:self-center">
                                            <button 
                                                type="button" 
                                                class="btn-decrement w-9 h-9 flex items-center justify-center font-bold text-base text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-300 rounded transition select-none"
                                                data-target="ticket_{{ $ticket->id }}"
                                            >
                                                -
                                            </button>
                                            
                                            <input 
                                                type="number" 
                                                id="ticket_{{ $ticket->id }}" 
                                                name="tickets[{{ $ticket->id }}]" 
                                                value="{{ $draftQty }}" 
                                                min="0" 
                                                max="{{ $ticket->quota }}" 
                                                class="ticket-qty w-14 h-9 text-center font-bold text-sm bg-white border border-slate-300 rounded focus:outline-none focus:ring-2 focus:ring-emerald-800"
                                            >

                                            <button 
                                                type="button" 
                                                class="btn-increment w-9 h-9 flex items-center justify-center font-bold text-base text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-300 rounded transition select-none"
                                                data-target="ticket_{{ $ticket->id }}"
                                            >
                                                +
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            </div>

            <!-- Right Column: Live Order Summary Card (Sticky) -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg border border-slate-200 p-6 shadow-xs sticky top-24 space-y-6">
                    <div>
                        <div class="text-xs font-bold uppercase tracking-wider text-emerald-800 mb-1">Rincian Pemesanan</div>
                        <h2 class="text-lg font-bold text-slate-900">Ringkasan Biaya</h2>
                    </div>

                    <div class="border-t border-slate-100 pt-4 space-y-3">
                        <div class="flex justify-between text-xs text-slate-600">
                            <span>Tanggal Kunjungan:</span>
                            <span id="summaryVisitDate" class="font-semibold text-slate-900">-</span>
                        </div>
                        <div class="flex justify-between text-xs text-slate-600">
                            <span>Total Jumlah Tiket:</span>
                            <span id="summaryTotalTickets" class="font-semibold text-slate-900">0 Tiket</span>
                        </div>
                    </div>

                    <!-- Itemized List Breakdown -->
                    <div class="border-t border-slate-100 pt-4">
                        <div class="text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Tiket Terpilih</div>
                        <div id="summaryItemsList" class="space-y-2 text-xs text-slate-600">
                            <div class="text-slate-400 italic">Belum ada tiket yang dipilih.</div>
                        </div>
                    </div>

                    <!-- Total Amount Banner -->
                    <div class="bg-slate-50 border border-slate-200 p-4 rounded-lg">
                        <div class="text-xs text-slate-500 uppercase font-semibold">Total Tagihan</div>
                        <div id="summaryGrandTotal" class="text-2xl font-black text-emerald-950 mt-1">
                            Rp 0
                        </div>
                        <div class="text-[11px] text-slate-500 mt-1">
                            Termasuk akses ke seluruh area konservasi dan taman tematik.
                        </div>
                    </div>

                    <!-- Notice for Guest Users -->
                    @guest
                        <div class="bg-emerald-50 border border-emerald-200 text-emerald-900 p-3 rounded text-xs leading-relaxed">
                            <span class="font-bold">Info Pengunjung:</span> Anda dapat langsung memilih tiket. Saat menekan tombol lanjutkan di bawah, Anda akan diarahkan untuk masuk atau mendaftar terlebih dahulu demi verifikasi tiket resmi Anda.
                        </div>
                    @endguest

                    <!-- Submit Button -->
                    <button 
                        type="submit" 
                        id="btnSubmitBooking" 
                        class="w-full bg-emerald-800 hover:bg-emerald-900 text-white font-bold text-sm uppercase tracking-wider py-3.5 px-4 rounded transition shadow-xs disabled:opacity-50 disabled:cursor-not-allowed"
                        disabled
                    >
                        Lanjut ke Pembayaran
                    </button>

                    <div class="text-center">
                        <span class="text-[11px] text-slate-400">
                            Transaksi aman & terverifikasi langsung oleh Pengelola Kebun Raya
                        </span>
                    </div>
                </div>
            </div>

        </div>
    </form>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const visitDateInput = document.getElementById('visit_date');
    const summaryVisitDate = document.getElementById('summaryVisitDate');
    const summaryTotalTickets = document.getElementById('summaryTotalTickets');
    const summaryItemsList = document.getElementById('summaryItemsList');
    const summaryGrandTotal = document.getElementById('summaryGrandTotal');
    const btnSubmit = document.getElementById('btnSubmitBooking');
    const ticketInputs = document.querySelectorAll('.ticket-qty');

    function formatRupiah(amount) {
        return 'Rp ' + amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function formatDateIndo(dateString) {
        if (!dateString) return '-';
        const [year, month, day] = dateString.split('-');
        const months = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        return `${parseInt(day)} ${months[parseInt(month) - 1]} ${year}`;
    }

    function recalculate() {
        let totalQty = 0;
        let grandTotal = 0;
        let itemsHtml = '';

        ticketInputs.forEach(input => {
            const card = input.closest('[data-ticket-id]');
            if (!card) return;

            const ticketId = card.getAttribute('data-ticket-id');
            const price = parseInt(card.getAttribute('data-price')) || 0;
            const title = card.querySelector('h3').innerText.trim();
            const qty = Math.max(0, parseInt(input.value) || 0);

            if (qty > 0) {
                const subtotal = price * qty;
                totalQty += qty;
                grandTotal += subtotal;

                itemsHtml += `
                    <div class="flex justify-between items-center py-1 border-b border-slate-100 last:border-0">
                        <div>
                            <div class="font-semibold text-slate-800">${title}</div>
                            <div class="text-[11px] text-slate-500">${qty} × ${formatRupiah(price)}</div>
                        </div>
                        <div class="font-bold text-slate-900">${formatRupiah(subtotal)}</div>
                    </div>
                `;
            }
        });

        // Update Summary UI
        summaryVisitDate.innerText = formatDateIndo(visitDateInput.value);
        summaryTotalTickets.innerText = `${totalQty} Tiket`;
        summaryGrandTotal.innerText = formatRupiah(grandTotal);

        if (totalQty > 0) {
            summaryItemsList.innerHTML = itemsHtml;
            btnSubmit.removeAttribute('disabled');
        } else {
            summaryItemsList.innerHTML = '<div class="text-slate-400 italic">Belum ada tiket yang dipilih.</div>';
            btnSubmit.setAttribute('disabled', 'disabled');
        }
    }

    // Event listener for date
    visitDateInput.addEventListener('change', recalculate);

    // Event listeners for quantity inputs
    ticketInputs.forEach(input => {
        input.addEventListener('input', recalculate);
        input.addEventListener('change', recalculate);
    });

    // Increment and decrement button handlers
    document.querySelectorAll('.btn-increment').forEach(btn => {
        btn.addEventListener('click', function () {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            if (input) {
                input.value = (parseInt(input.value) || 0) + 1;
                recalculate();
            }
        });
    });

    document.querySelectorAll('.btn-decrement').forEach(btn => {
        btn.addEventListener('click', function () {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            if (input) {
                const currentVal = parseInt(input.value) || 0;
                if (currentVal > 0) {
                    input.value = currentVal - 1;
                    recalculate();
                }
            }
        });
    });

    // Initial calculation on page load
    recalculate();
});
</script>
@endpush
@endsection
