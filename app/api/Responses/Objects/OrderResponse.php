<?php

namespace App\api\Responses\Objects;

use App\api\Responses\Helpers\ResponseBuilder;
use App\Models\Order;
use Illuminate\Database\Eloquent\Model;

class OrderResponse extends AbstractResponse implements InterfaceResponse
{
    const INCLUDE_OPTIONS = [self::CUSTOMER_INCLUDED, self::ITEMS_INCLUDED];
    const TYPE = 'orders';
    public int $order_id;
    public ?CustomerResponse $customer;
    public array $order_items = [];
    public string $status = Order::STATUS_ENUMS[Order::PENDING_STATUS];

    public function __construct(int $order_id, ?CustomerResponse $customer, array $order_items, string $status, string $created, string $updated, array $includedParams = [])
    {
        $this->order_id = $order_id;
        $this->customer = $customer;
        $this->order_items = $order_items;
        $this->status = $status;
        $this->created = $created;
        $this->updated = $updated;
        $this->includedParams = $includedParams;
    }


    public function getAsData(): array
    {
        $relationships = [];
        if (!empty($this->order_items)) {
            foreach ($this->order_items as $element) {
                $relationships[] = $element->getAsRelation();
            }

            $relationships[] = empty($this->customer) ? [] : $this->customer->getAsRelation();
        }

        $attributes = [
            'status' => $this->status,
            'created' => $this->created,
            'updated' => $this->updated,
        ];

        $relationships = array_filter($relationships, function ($subArray) {
            return !empty($subArray);
        });

        return ResponseBuilder::buildData(
            self::TYPE,
            $this->order_id,
            $attributes,
            $relationships
        );
    }

    public function getAsIncluded(): array
    {
        if (!$this->hasIncludedParams()) {
            return [];
        }

        $included = [];

        if ($this->elementIsInIncludedParams(self::ITEMS_INCLUDED)) {
            foreach ($this->order_items as $element) {
                $included[] = $element->getAsIncluded();
            }
        }

        if ($this->elementIsInIncludedParams(self::CUSTOMER_INCLUDED)) {
            $included[] = $this->customer->getAsIncluded();
        }

        return $included;
    }

    public function getAsRelation(): array
    {
        if (!$this->hasIncludedParams()) {
            return [];
        }

        $relationships = [];

        if ($this->elementIsInIncludedParams(self::ITEMS_INCLUDED)) {
            foreach ($this->order_items as $element) {
                $relationships[] = $element->getAsRelation();
            }
        }

        if ($this->elementIsInIncludedParams(self::CUSTOMER_INCLUDED)) {
            $relationships[] = $this->customer->getAsRelation();
        }

        return ResponseBuilder::buildRelationships(
            'order',
            self::TYPE,
            $this->order_id,
            $relationships
        );
    }

    private function elementIsInIncludedParams(string $element): bool
    {
        $included = array_intersect(self::INCLUDE_OPTIONS, $this->includedParams);
        return in_array($element, $included);
    }

    public static function createFromModel(Order|Model $model, array $includedParams = []): self
    {
        $included = array_intersect(self::INCLUDE_OPTIONS, $includedParams);
        $customer = null;
        $orderItems = [];
        if (in_array(self::CUSTOMER_INCLUDED, $included)) {
            $customer = CustomerResponse::createFromModel($model->customer()->first(), $includedParams);
        }

        if (in_array(self::ITEMS_INCLUDED, $included)) {
            foreach ($model->orderItems()->get() as $element) {
                $orderItems[] = OrderItemResponse::createFromModel($element, $includedParams);
            }
        }
        return new self(
            $model->id,
            $customer,
            $orderItems,
            $model->status,
            $model->created_at,
            $model->updated_at,
            $includedParams
        );
    }
}
