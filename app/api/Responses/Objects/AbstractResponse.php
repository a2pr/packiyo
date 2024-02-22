<?php

namespace App\api\Responses\Objects;

class AbstractResponse
{
    public string $created;
    public string $updated;
    public array $includedParams;

    protected function hasIncludedParams(): bool
    {
        return !empty($this->includedParams);
    }
}
