<?php

namespace App\api\Responses;

class OrdersRetrieveResponse extends AbstractResponse
{
    public array $orders;

    public function __construct(array $orders)
    {
        $this->orders = $orders;
    }

}
