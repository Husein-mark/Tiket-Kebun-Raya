<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'invoice_number',
        'verification_code',
        'user_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'visit_date',
        'total_amount',
        'payment_method',
        'payment_proof',
        'status',
        'admin_notes',
        'confirmed_at',
        'used_at',
    ];

    protected function casts(): array
    {
        return [
            'visit_date' => 'date',
            'total_amount' => 'integer',
            'confirmed_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function isConfirmed(): bool
    {
        return in_array($this->status, ['confirmed', 'used'], true);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isUsed(): bool
    {
        return $this->status === 'used';
    }

    public function canDownloadTicket(): bool
    {
        return in_array($this->status, ['confirmed', 'used'], true);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'confirmed' => 'LUNAS / TERVERIFIKASI',
            'used' => 'SUDAH DIGUNAKAN',
            'rejected' => 'DITOLAK / TIDAK VALID',
            default => $this->payment_method === 'cod' ? 'MENUNGGU PEMBAYARAN LOKET' : 'MENUNGGU KONFIRMASI',
        };
    }

    public function getFormattedTotalAttribute(): string
    {
        return 'Rp '.number_format($this->total_amount, 0, ',', '.');
    }

    public function getTotalTicketsAttribute(): int
    {
        return (int) $this->items->sum('quantity');
    }
}
