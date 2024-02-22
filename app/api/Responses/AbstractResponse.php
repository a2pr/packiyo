<?php

namespace App\api\Responses;

class AbstractResponse implements \JsonSerializable
{
    public array $data;
    public array $included;

    public function jsonSerialize(): mixed
    {
        $result = [
            'data' => $this->getData(),
        ];

        $included = $this->getIncluded();
        if($included){
            $result['included']  = $this->getIncluded();
        }

        return $result;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getIncluded(): array
    {
        return $this->included;
    }

}
