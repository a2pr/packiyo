<?php

namespace App\api\Responses;

use App\Models\Order;

class OrderResponse
{
    public int $order_id;
    public array $order_items;
    public int $customer_id;
    public string $status = Order::STATUS_ENUMS[Order::PENDING_STATUS];

    public function __construct(int $order_id, array $order_items, int $customer_id, string $status)
    {
        $this->order_id = $order_id;
        $this->order_items = $order_items;
        $this->customer_id = $customer_id;
        $this->status = $status;
    }
}
