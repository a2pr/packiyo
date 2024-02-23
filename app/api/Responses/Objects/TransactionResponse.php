<?php

namespace App\api\Responses\Objects;

use App\api\Responses\Helpers\ResponseBuilder;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Model;

class TransactionResponse extends AbstractResponse implements InterfaceResponse
{
    const TYPE = 'transactions';
    const INCLUDE_OPTIONS = [self::ITEMS_INCLUDED];
    public int $transaction_id;
    public int $quantity_request;
    public int $available_inventory;
    public int $updated_available_inventory;
    public int $status_operation;
    public array $order_items = [];

    public function __construct(int $transaction_id, int $quantity_request, int $available_inventory, int $updated_available_inventory, int $status_operation, array $order_items, string $created, string $updated, array $includedParams = [])
    {
        $this->transaction_id = $transaction_id;
        $this->quantity_request = $quantity_request;
        $this->available_inventory = $available_inventory;
        $this->updated_available_inventory = $updated_available_inventory;
        $this->status_operation = $status_operation;
        $this->order_items = $order_items;
        $this->created = $created;
        $this->updated = $updated;
        $this->includedParams = $includedParams;
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

        return ResponseBuilder::buildRelationships(
            'order',
            self::TYPE,
            $this->transaction_id,
            $relationships
        );
    }

    public function getAsData(): array
    {
        $relationships = [];
        if (!empty($this->order_items)) {
            foreach ($this->order_items as $element) {
                $relationships[] = $element->getAsRelation();
            }
        }

        $attributes = [
            'quantity_request' => $this->quantity_request,
            'available_inventory' => $this->available_inventory,
            'updated_available_inventory' => $this->updated_available_inventory,
            'status_operation' => $this->status_operation,
            'created_at,' => $this->created,
            'updated_at,' => $this->updated,
        ];

        $relationships = array_filter($relationships, function ($subArray) {
            return !empty($subArray);
        });

        return ResponseBuilder::buildData(
            self::TYPE,
            $this->transaction_id,
            $attributes,
            $relationships
        );
    }

    private function elementIsInIncludedParams(string $element): bool
    {
        $included = array_intersect(self::INCLUDE_OPTIONS, $this->includedParams);
        return in_array($element, $included);
    }

    public static function createFromModel(Transaction|Model $model, array $includedParams = []): InterfaceResponse
    {
        $included = array_intersect(self::INCLUDE_OPTIONS, $includedParams);
        $orderItems = [];

        if (in_array(self::ITEMS_INCLUDED, $included)) {
            $orderItems[] = OrderItemResponse::createFromModel($model->orderItem()->first(), $includedParams);

        }
        return new self(
            $model->id,
            $model->quantity_request,
            $model->available_inventory,
            $model->updated_available_inventory,
            $model->status_operation,
            $orderItems,
            $model->created_at,
            $model->updated_at,
            $includedParams
        );
    }
}
