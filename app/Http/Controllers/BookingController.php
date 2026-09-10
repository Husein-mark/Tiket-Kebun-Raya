<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Ticket;
use App\Services\QrCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BookingController extends Controller
{
    /**
     * Tampilan katalog tiket dan pemesanan utama.
     */
    public function index(): View
    {
        $tickets = Ticket::active()->get();
        $draft = session('booking_draft', null);

        return view('pages.home', compact('tickets', 'draft'));
    }

    /**
     * Menyimpan draf pesanan. Pengunjung tamu (belum login) tetap bisa memesan,
     * lalu diarahkan untuk login/register sebelum finalisasi pembayaran.
     */
    public function saveDraft(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'visit_date' => ['required', 'date', 'after_or_equal:today'],
            'tickets' => ['required', 'array'],
            'tickets.*' => ['nullable', 'integer', 'min:0'],
        ], [
            'visit_date.required' => 'Tanggal kunjungan wajib dipilih.',
            'visit_date.after_or_equal' => 'Tanggal kunjungan tidak boleh sebelum hari ini.',
            'tickets.required' => 'Pilih minimal satu tiket.',
        ]);

        $selectedTickets = array_filter($validated['tickets'], fn ($qty) => (int) $qty > 0);

        if (empty($selectedTickets)) {
            return back()->withInput()->withErrors([
                'tickets' => 'Silakan masukkan minimal 1 tiket untuk melanjutkan pemesanan.',
            ]);
        }

        // Hitung total harga server-side untuk memastikan integritas
        $ticketsData = Ticket::whereIn('id', array_keys($selectedTickets))->get()->keyBy('id');
        $totalAmount = 0;
        $items = [];

        foreach ($selectedTickets as $ticketId => $qty) {
            if ($ticket = $ticketsData->get($ticketId)) {
                $subtotal = $ticket->price * (int) $qty;
                $totalAmount += $subtotal;
                $items[$ticketId] = [
                    'id' => $ticket->id,
                    'name' => $ticket->name,
                    'price' => $ticket->price,
                    'quantity' => (int) $qty,
                    'subtotal' => $subtotal,
                ];
            }
        }

        session([
            'booking_draft' => [
                'visit_date' => $validated['visit_date'],
                'items' => $items,
                'total_amount' => $totalAmount,
            ],
        ]);

        if (! Auth::check()) {
            return redirect()->route('login')->with(
                'info',
                'Pemesanan tiket Anda telah tersimpan sementara. Silakan masuk atau daftar akun untuk menyelesaikan pembelian tiket.'
            );
        }

        return redirect()->route('checkout.page');
    }

    /**
     * Halaman checkout / konfirmasi pembayaran.
     */
    public function checkoutPage(): View|RedirectResponse
    {
        $draft = session('booking_draft');

        if (! $draft || empty($draft['items'])) {
            return redirect()->route('home')->with('error', 'Silakan pilih tanggal dan tiket terlebih dahulu.');
        }

        return view('pages.checkout', [
            'draft' => $draft,
            'user' => Auth::user(),
        ]);
    }

    /**
     * Memproses pesanan dari checkout (Transfer Bank atau COD).
     */
    public function processCheckout(Request $request): RedirectResponse
    {
        $draft = session('booking_draft');

        if (! $draft || empty($draft['items'])) {
            return redirect()->route('home')->with('error', 'Sesi pemesanan telah kedaluwarsa. Silakan pilih kembali tiket Anda.');
        }

        $validated = $request->validate([
            'payment_method' => ['required', 'in:transfer,cod'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'payment_proof' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'payment_method.required' => 'Pilih metode pembayaran yang diinginkan.',
            'customer_name.required' => 'Nama pemesan wajib diisi.',
            'customer_email.required' => 'Email pemesan wajib diisi.',
            'customer_phone.required' => 'Nomor telepon/WhatsApp wajib diisi.',
            'payment_proof.image' => 'Bukti pembayaran harus berupa file gambar (JPG, PNG, atau WEBP).',
            'payment_proof.max' => 'Ukuran file bukti pembayaran maksimal 2 MB.',
        ]);

        // Jika memilih transfer, bukti bayar wajib diunggah
        if ($validated['payment_method'] === 'transfer' && ! $request->hasFile('payment_proof')) {
            return back()->withInput()->withErrors([
                'payment_proof' => 'Silakan unggah foto bukti transfer untuk menyelesaikan pemesanan transfer bank.',
            ]);
        }

        $proofPath = null;
        if ($request->hasFile('payment_proof')) {
            $proofPath = $request->file('payment_proof')->store('payment_proofs', 'public');
        }

        // Generate nomor invoice unik
        do {
            $invoiceNumber = 'TKR-'.date('Ymd').'-'.strtoupper(Str::random(5));
        } while (Order::where('invoice_number', $invoiceNumber)->exists());

        // Generate kode verifikasi unik untuk QR Code
        $verificationCode = (string) Str::uuid();

        $order = Order::create([
            'invoice_number' => $invoiceNumber,
            'verification_code' => $verificationCode,
            'user_id' => Auth::id(),
            'customer_name' => $validated['customer_name'],
            'customer_email' => $validated['customer_email'],
            'customer_phone' => $validated['customer_phone'],
            'visit_date' => $draft['visit_date'],
            'total_amount' => $draft['total_amount'],
            'payment_method' => $validated['payment_method'],
            'payment_proof' => $proofPath,
            'status' => 'pending',
        ]);

        // Simpan rincian item pesanan
        foreach ($draft['items'] as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'ticket_id' => $item['id'],
                'ticket_name' => $item['name'],
                'unit_price' => $item['price'],
                'quantity' => $item['quantity'],
                'subtotal' => $item['subtotal'],
            ]);
        }

        // Hapus draf sesi setelah berhasil dibuat
        session()->forget('booking_draft');

        $message = $validated['payment_method'] === 'transfer'
            ? 'Pesanan berhasil dibuat! Bukti pembayaran Anda tersimpan dan berstatus Menunggu Konfirmasi dari Pengelola Kebun Raya.'
            : 'Pesanan berhasil dibuat! Silakan lakukan pembayaran tunai di Loket Kebun Raya pada tanggal kunjungan.';

        return redirect()->route('orders.show', $order->invoice_number)->with('success', $message);
    }

    /**
     * Riwayat transaksi pengunjung yang sedang login.
     */
    public function ordersHistory(): View
    {
        $orders = Auth::user()->orders()->with('items')->latest()->paginate(10);

        return view('pages.orders.history', compact('orders'));
    }

    /**
     * Tampilan detail pesanan / invoice.
     */
    public function showOrder(string $invoiceNumber): View
    {
        $order = Order::with('items', 'user')
            ->where('invoice_number', $invoiceNumber)
            ->firstOrFail();

        // Pastikan hanya pemilik pesanan atau admin yang dapat melihat
        if (Auth::id() !== $order->user_id && ! Auth::user()->isAdmin()) {
            abort(403, 'Anda tidak memiliki akses ke rincian pesanan ini.');
        }

        return view('pages.orders.show', compact('order'));
    }

    /**
     * Upload ulang atau perbarui bukti transfer jika status masih pending
     */
    public function updatePaymentProof(Request $request, string $invoiceNumber): RedirectResponse
    {
        $order = Order::where('invoice_number', $invoiceNumber)->firstOrFail();

        if (Auth::id() !== $order->user_id && ! Auth::user()->isAdmin()) {
            abort(403);
        }

        if ($order->isConfirmed()) {
            return back()->with('info', 'Pesanan ini sudah lunas terverifikasi.');
        }

        $request->validate([
            'payment_proof' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $proofPath = $request->file('payment_proof')->store('payment_proofs', 'public');
        $order->update([
            'payment_proof' => $proofPath,
            'status' => 'pending',
            'admin_notes' => null,
        ]);

        return back()->with('success', 'Bukti pembayaran baru berhasil diunggah dan sedang ditinjau pengelola.');
    }

    /**
     * Tampilan E-Ticket Digital dengan QR Code Aktif.
     * Sesuai kriteria LKPD #3, #4, dan #5: Hanya dapat diakses saat status sudah Lunas / Terverifikasi!
     */
    public function eTicket(string $invoiceNumber): View|RedirectResponse
    {
        $order = Order::with('items')
            ->where('invoice_number', $invoiceNumber)
            ->firstOrFail();

        if (Auth::id() !== $order->user_id && ! Auth::user()->isAdmin()) {
            abort(403, 'Akses ke E-Ticket ditolak.');
        }

        // LKPD Kriteria #3: Tombol unduh / akses terkunci bila belum dikonfirmasi admin
        if (! $order->canDownloadTicket()) {
            return redirect()->route('orders.show', $order->invoice_number)
                ->with('error', 'E-Ticket belum aktif. Tiket hanya dapat diunduh dan digunakan setelah pembayaran berstatus LUNAS / TERVERIFIKASI.');
        }

        // Generate QR code yang mengarah ke link verifikasi resmi Kebun Raya
        $verificationUrl = route('ticket.verify', $order->verification_code);
        $qrCodeSvg = QrCodeService::generateSvg($verificationUrl, 220, '#14532d', '#ffffff');

        return view('pages.orders.eticket', compact('order', 'qrCodeSvg', 'verificationUrl'));
    }
}
