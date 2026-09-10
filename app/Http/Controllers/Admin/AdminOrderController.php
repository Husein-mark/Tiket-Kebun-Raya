<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminOrderController extends Controller
{
    /**
     * Tampilan Ringkasan Metrik Dashboard Admin
     */
    public function dashboard(): View
    {
        $metrics = [
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'confirmed_orders' => Order::whereIn('status', ['confirmed', 'used'])->count(),
            'total_revenue' => (int) Order::whereIn('status', ['confirmed', 'used'])->sum('total_amount'),
            'total_tickets_created' => Ticket::count(),
            'active_tickets' => Ticket::where('is_active', true)->count(),
        ];

        $recentOrders = Order::with(['items', 'user'])->latest()->take(6)->get();

        return view('admin.dashboard', compact('metrics', 'recentOrders'));
    }

    /**
     * Daftar seluruh transaksi pemesanan tiket dengan filter status.
     */
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $query = Order::with(['items', 'user'])->latest();

        if ($status && in_array($status, ['pending', 'confirmed', 'rejected', 'used'])) {
            $query->where('status', $status);
        }

        $orders = $query->paginate(15)->withQueryString();

        $counts = [
            'all' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'confirmed' => Order::where('status', 'confirmed')->count(),
            'used' => Order::where('status', 'used')->count(),
            'rejected' => Order::where('status', 'rejected')->count(),
        ];

        return view('admin.orders.index', compact('orders', 'counts', 'status'));
    }

    /**
     * Detail transaksi untuk verifikasi bukti bayar manual oleh Admin.
     */
    public function show(Order $order): View
    {
        $order->load(['items.ticket', 'user']);

        return view('admin.orders.show', compact('order'));
    }

    /**
     * Tahap 3 LKPD: Admin menekan tombol Approve / Konfirmasi.
     * Status berubah menjadi "Lunas / Terverifikasi" (LKPD Checklist #4).
     */
    public function approve(Request $request, Order $order): RedirectResponse
    {
        $order->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
            'admin_notes' => $request->input('admin_notes', 'Pembayaran telah diverifikasi sah oleh Pengelola Kebun Raya.'),
        ]);

        return back()->with('success', "Pesanan {$order->invoice_number} berhasil dikonfirmasi! Status kini LUNAS / TERVERIFIKASI dan E-Ticket telah aktif.");
    }

    /**
     * Admin menolak bukti pembayaran (misal file tidak jelas / nominal salah).
     */
    public function reject(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'admin_notes' => ['required', 'string', 'max:500'],
        ], [
            'admin_notes.required' => 'Wajib memberikan alasan penolakan bukti bayar.',
        ]);

        $order->update([
            'status' => 'rejected',
            'admin_notes' => $validated['admin_notes'],
        ]);

        return back()->with('warning', "Pesanan {$order->invoice_number} ditolak. Pembeli dapat melihat catatan penolakan dan mengunggah ulang bukti bayar.");
    }

    /**
     * Check-in langsung dari daftar pesanan oleh admin
     */
    public function markAsUsed(Order $order): RedirectResponse
    {
        if ($order->status !== 'confirmed') {
            return back()->with('error', 'Hanya pesanan yang sudah Lunas yang dapat ditandai digunakan.');
        }

        $order->update([
            'status' => 'used',
            'used_at' => now(),
        ]);

        return back()->with('success', "Tiket untuk pesanan {$order->invoice_number} berhasil divalidasi Check-In!");
    }
}
