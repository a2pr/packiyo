<?php

namespace App\Http\Facades;

use App\api\Responses\ErrorResponse;
use App\api\Responses\Objects\OrderResponse;
use App\api\Responses\OrderCreateResponse;
use App\DataTransferObject\OrderInventoryDto;
use App\Events\OrderEvent;
use App\Models\Customer;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Exception;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class OrderFacade
{
    /**
     * @param array $data
     * @return array
     */
    public function processRequest(array $data): array
    {
        $statusCode = ResponseAlias::HTTP_CREATED;
        try {

            if(empty($data['products'])){
                throw new Exception(
                    'Empty products array',
                    ResponseAlias::HTTP_BAD_REQUEST
                );
            }
            $data['products'] = $this->groupProductDetailsInRequest($data);
            $customer = Customer::find($data['customer_id']);

            if (empty($customer)) {
                $this->throwMissingModelException('Customer');
            }

            //create order
            $order = new Order();
            $order->status = Order::PENDING_STATUS;
            $order->customer_id = $data['customer_id'];
            $order->save();

            $newStatus = Order::PENDING_STATUS;
            //create order items
            $orderItems = [];
            $updatedInventory = true;
            foreach ($data['products'] as $element) {
                $orderItem = new OrderItem();
                $orderItem->quantity = $element['quantity'];
                $orderItem->product_id = $element['id'];
                $orderItems[] = $orderItem;

                list($inventory, $updated_available_inventory) = $this->getUpdatedAvailableInventory($element);
                if ($updatedInventory) {
                    $updatedInventory = $updated_available_inventory > 0;
                }
            }

            if ($updatedInventory) {
                $newStatus = Order::READY_TO_SHIP_STATUS;
                foreach ($data['products'] as $element) {
                    list($inventory, $updated_available_inventory) = $this->getUpdatedAvailableInventory($element);
                    $inventory->update(['available_inventory' => $updated_available_inventory]);
                }
            }

            $order->orderItems()->saveMany($orderItems);
            $order->load('orderItems');

            if ($newStatus != Order::PENDING_STATUS) {
                $order->update(['status' => Order::READY_TO_SHIP_STATUS]);
            }

            $this->eventTransaction($order, $newStatus);

            $orderResponse = new OrderCreateResponse(OrderResponse::createFromModel($order, [OrderResponse::ITEMS_INCLUDED]));

        } catch (Exception $e) {
            Log::error($e->getMessage());
            $statusCode = $e->getCode();

            if ($statusCode < 400 || $statusCode > 500) {
                $statusCode = 500;
            }

            $orderResponse = new ErrorResponse(
                $statusCode,
                $e
            );
        }
        return array($statusCode, $orderResponse);
    }

    /**
     * @throws Exception
     */
    private function groupProductDetailsInRequest(array $data): array
    {
        $outputArray = [];
        foreach ($data['products'] as $item) {
            $productId = $item['id'];
            $this->validateQuantityValue($item['quantity']);

            $quantity = $item['quantity'];

            if (isset($outputArray[$productId])) {
                // If the id already exists, merge quantities
                $outputArray[$productId]['quantity'] += $quantity;
            } else {
                // Otherwise, create a new entry
                $outputArray[$productId] = ['id' => $productId, 'quantity' => $quantity];
            }
        }

        return $outputArray;
    }

    /**
     * @param Order $order
     * @param int $eventStatus
     * @return void
     */
    private function eventTransaction(Order $order, int $eventStatus): void
    {
        $orderInventoryDtos = [];
        foreach ($order->orderItems()->get() as $orderItem) {

            $product = $orderItem->products()->first();
            $inventory = $product->inventory()->first();

            $orderInventoryDtos[] = new OrderInventoryDto(
                $inventory->available_inventory,
                $inventory->available_inventory - $orderItem->quantity,
                $orderItem->quantity,
                $orderItem->id,
                $eventStatus
            );
        }

        OrderEvent::dispatch($orderInventoryDtos);
    }

    /**
     * @param string $modelName
     * @return void
     * @throws Exception
     */
    private function throwMissingModelException(string $modelName): void
    {
        throw new Exception(
            "$modelName not found",
            ResponseAlias::HTTP_BAD_REQUEST
        );
    }

    /**
     * @param int $id
     * @return Inventory
     * @throws Exception
     */
    private function getInventoryFromProductId(int $id): Inventory
    {
        $product = Product::find($id);
        if (empty($product)) {
            $this->throwMissingModelException('Product');
        }
        return $product->inventory()->first();
    }

    /**
     * @param mixed $element
     * @return array
     * @throws Exception
     */
    private function getUpdatedAvailableInventory(mixed $element): array
    {
        $inventory = $this->getInventoryFromProductId($element['id']);
        $updated_available_inventory = $inventory->available_inventory - $element['quantity'];
        return array($inventory, $updated_available_inventory);
    }

    /**
     * @param mixed $quantity
     * @return void
     * @throws Exception
     */
    private function validateQuantityValue(mixed $quantity): void
    {
        if (empty($quantity)) {
            throw new Exception(
                'Product quantity is missing',
                ResponseAlias::HTTP_BAD_REQUEST
            );
        }

        $quantity = (int)$quantity;

        if ($quantity == 0) {
            throw new Exception(
                'Product quantity needs to be an integer',
                ResponseAlias::HTTP_BAD_REQUEST
            );
        }

        if ($quantity < 0) {
            throw new Exception(
                'Product quantity cant be negative values',
                ResponseAlias::HTTP_BAD_REQUEST
            );
        }
    }


}
