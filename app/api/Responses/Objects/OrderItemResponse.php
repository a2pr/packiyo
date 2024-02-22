<?php

namespace App\api\Responses\Objects;

use App\api\Responses\Helpers\ResponseBuilder;

class OrderItemResponse implements InterfaceResponse
{
    const TYPE = 'order-items';
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

    public function getAsData(): array
    {
        $attributes = [
            "quantity" => $this->quantity,
            "created" => $this->created,
            "updated" => $this->updated
        ];

        return ResponseBuilder::buildData(
            self::TYPE,
            $this->id,
            $attributes,
            $this->product->getAsRelation()
        );
    }

    public function getAsIncluded(): array
    {
        return ResponseBuilder::buildRelationships(
            'order-item',
            self::TYPE,
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
            self::TYPE,
            $this->id,
            $attributes,
            $this->product->getAsRelation()
        );
    }

    public static function createFromModel($model): self
    {
        $productResponse = ProductResponse::createFromModel($model->products()->first());
        return new self(
            $model->id,
            $productResponse,
            $model->quantity,
            $model->created_at,
            $model->updated_at,
        );
    }
}
