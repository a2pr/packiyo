<?php

namespace App\api\Responses\Objects;

use App\api\Responses\Helpers\ResponseBuilder;

class OrderItemResponse implements InterfaceResponse
{
    public int $id;
    public ProductResponse $product;
    public int $quantity;
    public string $created;
    public string $updated;

    public function __construct(int $id, ProductResponse $product, int $quantity, string $created, string $updated)
    {
        $this->id = $id;
        $this->product = $product;
        $this->quantity = $quantity;
        $this->created = $created;
        $this->updated = $updated;
    }

    public function getAsIncluded(): array
    {
        return ResponseBuilder::buildRelationships(
            'order-item',
            'order-items',
            $this->id,
            $this->product->getAsRelation()
        );
    }

    public function getAsRelation(): array
    {
        $attributes = [
            "quantity" => $this->quantity,
            "created" => $this->created,
            "updated" => $this->updated
        ];

        return ResponseBuilder::buildIncluded(
            'customers',
            $this->id,
            $attributes,
            $this->product->getAsRelation()
        );
    }
}
