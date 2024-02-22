<?php

namespace App\DataTransferObject;

class OrderInventoryDto
{
    public int $available_quantity;
    public int $updated_available_quantity;
    public int $quantity_request;
    public int $order_item_id;
    public bool $status;

    public function __construct(int $available_quantity, int $updated_available_quantity, int $quantity_request, int $order_item_id, bool $status)
    {
        $this->available_quantity = $available_quantity;
        $this->updated_available_quantity = $updated_available_quantity;
        $this->quantity_request = $quantity_request;
        $this->order_item_id = $order_item_id;
        $this->status = $status;
    }

    public function getAvailableQuantity(): int
    {
        return $this->available_quantity;
    }

    public function getUpdatedAvailableQuantity(): int
    {
        return $this->updated_available_quantity;
    }

    public function getQuantityRequest(): int
    {
        return $this->quantity_request;
    }

    public function getOrderItemId(): int
    {
        return $this->order_item_id;
    }

    public function isStatus(): bool
    {
        return $this->status;
    }

}
