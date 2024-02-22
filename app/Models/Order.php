<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    const PENDING_STATUS = 0;
    const READY_TO_SHIP_STATUS = 1;

    const STATUS_ENUMS = [
        self::PENDING_STATUS => 'pending',
        self::READY_TO_SHIP_STATUS => 'ready_to_ship',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['status'];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class,'customer_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class,'order_id');
    }
}
