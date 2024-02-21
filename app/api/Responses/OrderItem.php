<?php

namespace App\api\Responses;

class OrderItem
{
    public $product_id;
    public $quantity;
    public $order_id;

    public function __construct($product_id, $quantity, $order_id)
    {
        $this->product_id = $product_id;
        $this->quantity = $quantity;
        $this->order_id = $order_id;
    }
}
