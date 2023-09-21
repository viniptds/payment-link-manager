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

    protected $fillable = [
        'id', 'value', 'description', 'status', 'max_installments', 'created_by', 'expire_at', 'cancelled_at', 'paid_at'
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function isExpired()
    {
        return $this->expire_at && $this->expire_at < date('Y-m-d H:i:s');
    }

    protected function getStatusAttribute($value)
    {
        if ($value == self::STATUS_ACTIVE && $this->isExpired()) {
            $value = self::STATUS_EXPIRED;
        }

        return $value;
    }

    protected function getMaxInstallmentsAttribute($value)
    {
        if (is_null($value)) {
            $value = env('CIELO_MAX_INSTALLMENTS', 12);
        }
        return $value;
    }
}
