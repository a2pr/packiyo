<?php

namespace App\api\Responses\Objects;

use App\api\Responses\Helpers\ResponseBuilder;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Model;

class OrderItemResponse extends AbstractResponse implements InterfaceResponse
{
    const INCLUDE_OPTIONS = [self::PRODUCT_INCLUDED];
    const TYPE = 'order-items';
    public int $id;
    public ?ProductResponse $product;
    public int $quantity;

    public function __construct(int $id, ?ProductResponse $product, int $quantity, string $created, string $updated, array $includedParams = [])
    {
        $this->id = $id;
        $this->product = $product;
        $this->quantity = $quantity;
        $this->created = $created;
        $this->updated = $updated;
        $this->includedParams = $includedParams;
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
            empty($this->product) ? [] : $this->product->getAsRelation()
        );
    }

    public function getAsRelation(): array
    {
        if (!$this->hasIncludedParams()) {
            return [];
        }

        $relationships = [];
        if ($this->elementIsInIncludedParams(self::PRODUCT_INCLUDED)) {
            $relationships[] = $this->product->getAsRelation();
        }

        return ResponseBuilder::buildRelationships(
            'order-item',
            self::TYPE,
            $this->id,
            $relationships
        );
    }

    public function getAsIncluded(): array
    {
        if (!$this->hasIncludedParams()) {
            return [];
        }

        $relationships = [];
        if ($this->elementIsInIncludedParams(self::PRODUCT_INCLUDED)) {
            $relationships[] = $this->product->getAsRelation();

        }

        $attributes = [
            "quantity" => $this->quantity,
            "created" => $this->created,
            "updated" => $this->updated
        ];

        return ResponseBuilder::buildIncluded(
            self::TYPE,
            $this->id,
            $attributes,
            $relationships
        );
    }

    private function elementIsInIncludedParams(string $element): bool
    {
        $included = array_intersect(self::INCLUDE_OPTIONS, $this->includedParams);
        return in_array($element, $included);
    }

    public static function createFromModel(OrderItem|Model $model, array $includedParams = []): self
    {
        $included = array_intersect(self::INCLUDE_OPTIONS, $includedParams);
        $productResponse = null;
        if (in_array(self::PRODUCT_INCLUDED, $included)) {
            $productResponse = ProductResponse::createFromModel($model->products()->first(), $includedParams);
        }

        return new self(
            $model->id,
            $productResponse,
            $model->quantity,
            $model->created_at,
            $model->updated_at,
            $includedParams
        );
    }
}
