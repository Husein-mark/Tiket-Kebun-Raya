use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Jalankan fresh seeder pada setiap pengujian
    $this->seed(DatabaseSeeder::class);
});

test('1. DatabaseSeeder hanya membuat akun admin dan akun pembeli tanpa produk tiket', function () {
    // Verifikasi akun admin
    $admin = User::where('email', 'admin@kebunraya.id')->first();
    expect($admin)->not->toBeNull()
        ->and($admin->isAdmin())->toBeTrue();

    // Verifikasi akun pembeli
    $buyer = User::where('email', 'pembeli@kebunraya.id')->first();
    expect($buyer)->not->toBeNull()
        ->and($buyer->role)->toBe('user');

    // Verifikasi TIDAK ADA tiket pada seeder
    expect(Ticket::count())->toBe(0);
});

test('2. Admin dapat membuat produk tiket baru secara mandiri di panel pengelola', function () {
    $admin = User::where('role', 'admin')->first();

    $response = $this->actingAs($admin)->post(route('admin.tickets.store'), [
        'name' => 'Tiket Masuk Domestik Dewasa',
        'category' => 'Wisatawan Domestik (Dewasa)',
        'price' => 25000,
        'quota' => 500,
        'description' => 'Akses lengkap ke seluruh area taman koleksi botani.',
        'terms' => 'Wajib tunjukkan identitas KTP.',
        'is_active' => '1',
    ]);

    $response->assertRedirect(route('admin.tickets.index'));
    expect(Ticket::count())->toBe(1);

    $ticket = Ticket::first();
    expect($ticket->name)->toBe('Tiket Masuk Domestik Dewasa')
        ->and($ticket->price)->toBe(25000)
        ->and($ticket->is_active)->toBeTrue();
});

test('3. Pengunjung dapat melihat katalog tiket aktif di halaman utama', function () {
    Ticket::create([
        'name' => 'Tiket Masuk Domestik Dewasa',
        'category' => 'Wisatawan Domestik',
        'price' => 25000,
        'quota' => 1000,
        'is_active' => true,
    ]);

    $response = $this->get(route('home'));

    $response->assertStatus(200)
        ->assertSee('Tiket Masuk Domestik Dewasa')
        ->assertSee('Rp 25.000');
});

test('4. LKPD Checklist #1: User memilih tanggal & jumlah tiket, total harga dihitung otomatis & draft tersimpan', function () {
    $ticketA = Ticket::create([
        'name' => 'Tiket Reguler Dewasa',
        'category' => 'Domestik',
        'price' => 25000,
        'quota' => 500,
        'is_active' => true,
    ]);

    $ticketB = Ticket::create([
        'name' => 'Tiket Reguler Anak',
        'category' => 'Domestik',
        'price' => 15000,
        'quota' => 500,
        'is_active' => true,
    ]);

    $visitDate = now()->addDays(2)->format('Y-m-d');

    // Tamu (guest) memilih 2 tiket dewasa dan 1 tiket anak
    $response = $this->post(route('booking.draft'), [
        'visit_date' => $visitDate,
        'tickets' => [
            $ticketA->id => 2,
            $ticketB->id => 1,
        ],
    ]);

    // Total yang diharapkan: (2 * 25.000) + (1 * 15.000) = 65.000
    $response->assertRedirect(route('login'));
    $this->assertSessionHas('booking_draft');

    $draft = session('booking_draft');
    expect($draft['total_amount'])->toBe(65000)
        ->and($draft['visit_date'])->toBe($visitDate)
        ->and(count($draft['items']))->toBe(2);
});

test('5. Pengunjung belum login diarahkan ke login dan setelah login draf pemesanan tetap utuh', function () {
    $ticket = Ticket::create([
        'name' => 'Tiket Domestik',
        'category' => 'Domestik',
        'price' => 20000,
        'quota' => 100,
        'is_active' => true,
    ]);

    // Guest drafts order
    $this->post(route('booking.draft'), [
        'visit_date' => now()->addDay()->format('Y-m-d'),
        'tickets' => [$ticket->id => 3],
    ]);

    // Guest logs in using buyer account
    $loginResponse = $this->post('/login', [
        'email' => 'pembeli@kebunraya.id',
        'password' => 'password',
    ]);

    // Should redirect directly to checkout page with draft intact
    $loginResponse->assertRedirect(route('checkout.page'));

    $checkoutResponse = $this->actingAs(User::where('email', 'pembeli@kebunraya.id')->first())
        ->get(route('checkout.page'));

    $checkoutResponse->assertStatus(200)
        ->assertSee('Tiket Domestik')
        ->assertSee('Rp 60.000');
});

test('6. LKPD Checklist #2: User mengunggah bukti bayar transfer, file tersimpan & status menjadi pending', function () {
    Storage::fake('public');

    $buyer = User::where('email', 'pembeli@kebunraya.id')->first();
    $ticket = Ticket::create([
        'name' => 'Tiket Masuk Dewasa',
        'category' => 'Domestik',
        'price' => 30000,
        'quota' => 100,
        'is_active' => true,
    ]);

    // Set draft in session
    session([
        'booking_draft' => [
            'visit_date' => now()->addDays(3)->format('Y-m-d'),
            'items' => [
                [
                    'id' => $ticket->id,
                    'name' => $ticket->name,
                    'price' => 30000,
                    'quantity' => 2,
                    'subtotal' => 60000,
                ],
            ],
            'total_amount' => 60000,
        ],
    ]);

    $file = UploadedFile::fake()->image('bukti_transfer.jpg');

    $response = $this->actingAs($buyer)->post(route('checkout.process'), [
        'payment_method' => 'transfer',
        'customer_name' => $buyer->name,
        'customer_email' => $buyer->email,
        'customer_phone' => '08123456789',
        'payment_proof' => $file,
    ]);

    $order = Order::first();
    expect($order)->not->toBeNull()
        ->and($order->status)->toBe('pending')
        ->and($order->payment_method)->toBe('transfer')
        ->and($order->total_amount)->toBe(60000)
        ->and($order->payment_proof)->not->toBeNull();

    Storage::disk('public')->assertExists($order->payment_proof);
    $response->assertRedirect(route('orders.show', $order->invoice_number));
});

test('7. User dapat memesan tiket dengan metode COD (Bayar di Tempat)', function () {
    $buyer = User::where('email', 'pembeli@kebunraya.id')->first();
    $ticket = Ticket::create([
        'name' => 'Tiket Reguler',
        'category' => 'Domestik',
        'price' => 20000,
        'quota' => 100,
        'is_active' => true,
    ]);

    session([
        'booking_draft' => [
            'visit_date' => now()->addDays(2)->format('Y-m-d'),
            'items' => [
                [
                    'id' => $ticket->id,
                    'name' => $ticket->name,
                    'price' => 20000,
                    'quantity' => 1,
                    'subtotal' => 20000,
                ],
            ],
            'total_amount' => 20000,
        ],
    ]);

    $response = $this->actingAs($buyer)->post(route('checkout.process'), [
        'payment_method' => 'cod',
        'customer_name' => $buyer->name,
        'customer_email' => $buyer->email,
        'customer_phone' => '08987654321',
    ]);

    $order = Order::first();
    expect($order)->not->toBeNull()
        ->and($order->payment_method)->toBe('cod')
        ->and($order->status)->toBe('pending');

    $response->assertRedirect(route('orders.show', $order->invoice_number));
});

test('8. LKPD Checklist #3: User mencoba unduh tiket sebelum konfirmasi, tombol unduh terkunci / disabled', function () {
    $buyer = User::where('email', 'pembeli@kebunraya.id')->first();

    $order = Order::create([
        'invoice_number' => 'TKR-20260910-TEST1',
        'verification_code' => 'test-code-12345',
        'user_id' => $buyer->id,
        'customer_name' => $buyer->name,
        'customer_email' => $buyer->email,
        'customer_phone' => '08123456789',
        'visit_date' => now()->addDay(),
        'total_amount' => 50000,
        'payment_method' => 'transfer',
        'status' => 'pending', // Belum dikonfirmasi admin
    ]);

    // Akses halaman show pesanan
    $response = $this->actingAs($buyer)->get(route('orders.show', $order->invoice_number));

    // Tombol unduh harus disabled/terkunci di halaman show
    $response->assertStatus(200)
        ->assertSee('Terkunci - Wajib Menunggu Konfirmasi Lunas Admin')
        ->assertSee('Download E-Ticket (Terkunci)');

    // Jika user mencoba langsung akses route e-ticket, sistem mengalihkan kembali
    $eTicketResponse = $this->actingAs($buyer)->get(route('orders.eticket', $order->invoice_number));
    $eTicketResponse->assertRedirect(route('orders.show', $order->invoice_number));
    $this->assertFalse($order->canDownloadTicket());
});

test('9. LKPD Checklist #4: Admin menekan tombol Konfirmasi, status berubah menjadi Lunas', function () {
    $admin = User::where('role', 'admin')->first();
    $buyer = User::where('email', 'pembeli@kebunraya.id')->first();

    $order = Order::create([
        'invoice_number' => 'TKR-20260910-CONF1',
        'verification_code' => 'confirm-code-uuid-1',
        'user_id' => $buyer->id,
        'customer_name' => $buyer->name,
        'customer_email' => $buyer->email,
        'visit_date' => now()->addDay(),
        'total_amount' => 45000,
        'payment_method' => 'transfer',
        'status' => 'pending',
    ]);

    $response = $this->actingAs($admin)->post(route('admin.orders.approve', $order->id));

    $order->refresh();
    expect($order->status)->toBe('confirmed')
        ->and($order->isConfirmed())->toBeTrue()
        ->and($order->confirmed_at)->not->toBeNull();

    $response->assertSessionHas('success');
});

test('10. LKPD Checklist #5: User menekan tombol Download Tiket, berkas E-Ticket berhasil diakses dengan QR Code aktif', function () {
    $buyer = User::where('email', 'pembeli@kebunraya.id')->first();
    $ticket = Ticket::create([
        'name' => 'Tiket Masuk Domestik Dewasa',
        'category' => 'Domestik',
        'price' => 25000,
        'quota' => 100,
        'is_active' => true,
    ]);

    $order = Order::create([
        'invoice_number' => 'TKR-20260910-ETIK1',
        'verification_code' => 'eticket-uuid-active',
        'user_id' => $buyer->id,
        'customer_name' => $buyer->name,
        'customer_email' => $buyer->email,
        'visit_date' => now()->addDays(2),
        'total_amount' => 50000,
        'payment_method' => 'transfer',
        'status' => 'confirmed', // Lunas terkonfirmasi
        'confirmed_at' => now(),
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'ticket_id' => $ticket->id,
        'ticket_name' => $ticket->name,
        'unit_price' => 25000,
        'quantity' => 2,
        'subtotal' => 50000,
    ]);

    // Halaman show menampilkan tombol aktif
    $showResponse = $this->actingAs($buyer)->get(route('orders.show', $order->invoice_number));
    $showResponse->assertStatus(200)
        ->assertSee('Download / Cetak E-Ticket');

    // User mengakses E-Ticket
    $eTicketResponse = $this->actingAs($buyer)->get(route('orders.eticket', $order->invoice_number));

    $eTicketResponse->assertStatus(200)
        ->assertSee('E-Ticket Sah Terbit')
        ->assertSee('KEBUN RAYA INDONESIA')
        ->assertSee('TKR-20260910-ETIK1')
        ->assertSee('Pindai Untuk Verifikasi')
        ->assertSee('<svg', false); // Memastikan vector QR code ter-render
});

test('11. Pemindaian QR Code membuka portal verifikasi resmi Kebun Raya', function () {
    $buyer = User::where('email', 'pembeli@kebunraya.id')->first();

    $order = Order::create([
        'invoice_number' => 'TKR-20260910-QRTEST',
        'verification_code' => 'public-verify-uuid-xyz',
        'user_id' => $buyer->id,
        'customer_name' => 'Budi Santoso',
        'customer_email' => 'budi@example.com',
        'visit_date' => now()->addDay(),
        'total_amount' => 25000,
        'payment_method' => 'transfer',
        'status' => 'confirmed',
        'confirmed_at' => now(),
    ]);

    // Akses publik route verifikasi yang disematkan pada QR Code
    $response = $this->get(route('ticket.verify', $order->verification_code));

    $response->assertStatus(200)
        ->assertSee('TIKET RESMI TERVERIFIKASI')
        ->assertSee('TKR-20260910-QRTEST')
        ->assertSee('Budi Santoso')
        ->assertSee('Status: VALID - BELUM DIGUNAKAN');
});

test('12. Petugas/Admin dapat melakukan validasi check-in pengunjung di portal verifikasi', function () {
    $admin = User::where('role', 'admin')->first();
    $buyer = User::where('email', 'pembeli@kebunraya.id')->first();

    $order = Order::create([
        'invoice_number' => 'TKR-20260910-GATE1',
        'verification_code' => 'gate-checkin-uuid',
        'user_id' => $buyer->id,
        'customer_name' => 'Budi Santoso',
        'customer_email' => 'budi@example.com',
        'visit_date' => now(),
        'total_amount' => 25000,
        'payment_method' => 'transfer',
        'status' => 'confirmed',
    ]);

    $response = $this->actingAs($admin)->post(route('ticket.checkin', $order->verification_code));

    $order->refresh();
    expect($order->status)->toBe('used')
        ->and($order->used_at)->not->toBeNull();

    $verifyResponse = $this->get(route('ticket.verify', $order->verification_code));
    $verifyResponse->assertSee('TIKET RESMI - SUDAH DIGUNAKAN');
});

test('13. Admin dapat menolak bukti bayar dan pembeli dapat melihat alasan penolakan', function () {
    $admin = User::where('role', 'admin')->first();
    $buyer = User::where('email', 'pembeli@kebunraya.id')->first();

    $order = Order::create([
        'invoice_number' => 'TKR-20260910-REJ1',
        'verification_code' => 'reject-code-uuid',
        'user_id' => $buyer->id,
        'customer_name' => $buyer->name,
        'customer_email' => $buyer->email,
        'visit_date' => now()->addDay(),
        'total_amount' => 30000,
        'payment_method' => 'transfer',
        'status' => 'pending',
    ]);

    $response = $this->actingAs($admin)->post(route('admin.orders.reject', $order->id), [
        'admin_notes' => 'Nominal transfer tidak sesuai dengan total tagihan.',
    ]);

    $order->refresh();
    expect($order->status)->toBe('rejected')
        ->and($order->admin_notes)->toBe('Nominal transfer tidak sesuai dengan total tagihan.');

    $showResponse = $this->actingAs($buyer)->get(route('orders.show', $order->invoice_number));
    $showResponse->assertSee('Bukti Pembayaran Ditolak oleh Pengelola')
        ->assertSee('Nominal transfer tidak sesuai dengan total tagihan.');
});

