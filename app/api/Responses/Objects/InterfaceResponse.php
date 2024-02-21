<?php

namespace App\api\Responses\Objects;

interface InterfaceResponse
{
    public function getAsIncluded(): array;

    public function getAsRelation(): array;
}
