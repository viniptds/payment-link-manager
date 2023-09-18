<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\UUID;

class Payment extends Model
{
    use HasFactory, UUID;

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_EXPIRED = 'expired';
    const STATUS_PAID = 'paid';
    const STATUS_CANCELLED = 'cancelled';

    const STEP_PERSONAL = 'personal';
    const STEP_ADDRESS = 'address';
    const STEP_CARD = 'card';

    const STEPS = [
        self::STEP_PERSONAL,
        self::STEP_CARD
    ];

    protected $fillable = [
        'id', 'value', 'description', 'status', 'transaction_log', 'expire_at', 'cancelled_at', 'paid_at'
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}