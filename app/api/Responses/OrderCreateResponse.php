<?php

namespace App\api\Responses;

use App\api\Responses\Objects\OrderResponse;

class OrderCreateResponse extends AbstractResponse
{
    public OrderResponse $order;

    public function __construct(OrderResponse $order)
    {
        $this->order = $order;
    }


    public function getData(): array
    {
        return $this->order->getAsData();
    }

    public function getIncluded(): array
    {
        return $this->order->getAsIncluded();
    }
}
