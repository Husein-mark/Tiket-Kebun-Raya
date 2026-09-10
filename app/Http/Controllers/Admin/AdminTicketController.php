<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminTicketController extends Controller
{
    public function index(): View
    {
        $tickets = Ticket::latest()->paginate(15);

        return view('admin.tickets.index', compact('tickets'));
    }

    public function create(): View
    {
        return view('admin.tickets.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'terms' => ['nullable', 'string'],
            'quota' => ['required', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        Ticket::create($validated);

        return redirect()->route('admin.tickets.index')->with('success', 'Produk tiket baru berhasil dibuat!');
    }

    public function edit(Ticket $ticket): View
    {
        return view('admin.tickets.edit', compact('ticket'));
    }

    public function update(Request $request, Ticket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'terms' => ['nullable', 'string'],
            'quota' => ['required', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $ticket->update($validated);

        return redirect()->route('admin.tickets.index')->with('success', 'Produk tiket berhasil diperbarui.');
    }

    public function toggleStatus(Ticket $ticket): RedirectResponse
    {
        $ticket->update([
            'is_active' => ! $ticket->is_active,
        ]);

        $status = $ticket->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Status tiket {$ticket->name} berhasil {$status}.");
    }

    public function destroy(Ticket $ticket): RedirectResponse
    {
        // Jika tiket sudah memiliki relasi order_item, jangan hard-delete, nonaktifkan saja
        if ($ticket->orderItems()->exists()) {
            $ticket->update(['is_active' => false]);

            return back()->with('info', 'Tiket ini telah memiliki riwayat pesanan, status dialihkan menjadi Non-Aktif.');
        }

        $ticket->delete();

        return redirect()->route('admin.tickets.index')->with('success', 'Produk tiket berhasil dihapus.');
    }
}
