<?php

namespace App\api\Responses\Objects;

use Illuminate\Database\Eloquent\Model;

interface InterfaceResponse
{
    const CUSTOMER_INCLUDED = 'customer';
    const PRODUCT_INCLUDED = 'product';
    const ITEMS_INCLUDED = 'items';

    public function getAsIncluded(): array;

    public function getAsRelation(): array;

    public function getAsData(): array;

    public static function createFromModel(Model $model, array $includedParams = []): self;
}
