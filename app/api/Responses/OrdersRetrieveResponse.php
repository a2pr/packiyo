<?php

namespace App\api\Responses;

class OrdersRetrieveResponse extends AbstractResponse
{
    public array $orders;

    public function __construct(array $orders)
    {
        $this->orders = $orders;
    }

    public function getData(): array
    {
        $data = [];
        foreach ($this->orders as $order){
            $data[] = $order->getAsData();
        }

        return $data;
    }

    public function getIncluded(): array
    {
        $data = [];
        foreach ($this->orders as $order){
            $data[] = $order->getAsIncluded();
        }

        return $data;
    }
}
