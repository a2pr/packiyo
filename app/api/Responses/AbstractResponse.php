<?php

namespace App\api\Responses;

class AbstractResponse implements \JsonSerializable
{
    public function jsonSerialize(): mixed
    {
        return get_object_vars($this);
    }
}
