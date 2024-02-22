<?php

namespace App\api\Responses\Objects;

interface InterfaceResponse
{
    public function getAsIncluded(): array;

    public function getAsRelation(): array;
    public function getAsData(): array;

    public static function createFromModel($model):self;
}
