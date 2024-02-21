<?php

namespace App\api\Responses;

class OrderCreateResponse extends AbstractResponse
{
    public bool $operation_status;
    public OrderResponse $order;

    public function __construct(bool $operation_status, OrderResponse $order)
    {
        $this->operation_status = $operation_status;
        $this->order = $order;
    }

}
