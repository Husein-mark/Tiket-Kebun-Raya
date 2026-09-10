<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class VerificationController extends Controller
{
    /**
     * Halaman portal verifikasi resmi tiket Kebun Raya (hasil pemindaian QR Code).
     */
    public function verify(string $verificationCode): View
    {
        $order = Order::with(['items', 'user'])
            ->where('verification_code', $verificationCode)
            ->first();

        return view('pages.verification', compact('order', 'verificationCode'));
    }

    /**
     * Fitur check-in tiket oleh petugas / administrator di gerbang masuk.
     */
    public function checkIn(Request $request, string $verificationCode): RedirectResponse
    {
        if (! Auth::check() || ! Auth::user()->isAdmin()) {
            abort(403, 'Hanya petugas pengelola Kebun Raya yang dapat melakukan validasi check-in tiket.');
        }

        $order = Order::where('verification_code', $verificationCode)->firstOrFail();

        if ($order->status === 'used') {
            return back()->with('info', 'Tiket ini sudah pernah digunakan sebelumnya pada '.$order->used_at?->translatedFormat('d F Y, H:i').' WIB.');
        }

        if ($order->status !== 'confirmed') {
            return back()->with('error', 'Tiket belum dapat digunakan karena belum berstatus Lunas.');
        }

        $order->update([
            'status' => 'used',
            'used_at' => now(),
        ]);

        return back()->with('success', 'Tiket berhasil divalidasi! Pengunjung dipersilakan masuk.');
    }
}
