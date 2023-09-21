<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\UUID;

class GatewayOperation extends Model
{
    use HasFactory, UUID;

    const VOID_OPERATION = 'void';
    const PAY_OPERATION = 'pay';

    protected $fillable = ['id', 'log', 'payment_id', 'gateway', 'type'];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
