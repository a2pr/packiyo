<?php

namespace App\api\Responses\Objects;

use App\api\Responses\Helpers\ResponseBuilder;
use App\Models\Order;

class OrderResponse implements InterfaceResponse
{
    const TYPE = 'orders';
    public int $order_id;
    public CustomerResponse $customer;
    public array $order_items;
    public string $status = Order::STATUS_ENUMS[Order::PENDING_STATUS];
    public string $created;
    public string $updated;

    public function __construct(int $order_id, CustomerResponse $customer, array $order_items, string $status, string $created, string $updated)
    {
        $this->order_id = $order_id;
        $this->customer = $customer;
        $this->order_items = $order_items;
        $this->status = $status;
        $this->created = $created;
        $this->updated = $updated;
    }

    public function getAsData(): array
    {
        $relationships = [];
        foreach ($this->order_items as $element) {
            $relationships[] = $element->getAsRelation();
        }

        $relationships[] = $this->customer->getAsRelation();

        $attributes = [
            'status' => $this->status,
            'created' => $this->created,
            'updated' => $this->updated,
        ];

        return ResponseBuilder::buildData(
            self::TYPE,
            $this->order_id,
            $attributes,
            $relationships
        );
    }

    public function getAsIncluded(): array
    {
        $relationships = [];
        foreach ($this->order_items as $element) {
            $relationships[] = $element->getAsRelation();
        }

        $relationships[] = $this->customer->getAsRelation();

        $attributes = [
            'status' => $this->status,
            'created' => $this->created,
            'updated' => $this->updated,
        ];

        return ResponseBuilder::buildIncluded(
            self::TYPE,
            $this->order_id,
            $attributes,
            $relationships
        );
    }

    public function getAsRelation(): array
    {
        $relationships = [];
        foreach ($this->order_items as $orderItem) {
            $relationships[] = $orderItem->getAsRelation();
        }

        $relationships[] = $this->customer->getAsRelation();

        return ResponseBuilder::buildRelationships(
            'order',
            self::TYPE,
            $this->order_id,
            $relationships
        );
    }
}
