<?php

namespace App\api\Responses;

use App\api\Responses\Objects\OrderResponse;

class OrderRetrieveResponse extends OrderResponse
{
    public array $orderResponses;

    public function getData()
    {
        $result = [];
        foreach ($this->orderResponses as $element){

        }
        return;
    }
}
