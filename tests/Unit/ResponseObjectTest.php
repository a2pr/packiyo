<?php

namespace Tests\Unit;

use App\api\Responses\Objects\CustomerResponse;
use App\api\Responses\Objects\OrderItemResponse;
use App\api\Responses\Objects\OrderResponse;
use App\api\Responses\Objects\ProductResponse;
use App\api\Responses\Objects\TransactionResponse;
use App\api\Responses\OrderRetrieveResponse;
use App\Models\Customer;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Transaction;
use Tests\TestCase;

class ResponseObjectTest extends TestCase
{
    public function testCustomerResponse()
    {
        $customer = Customer::factory(1)->create();
        $customerResponse = CustomerResponse::createFromModel($customer->first());
        $customerData = $customerResponse->getAsData();
        $customerIncluded = $customerResponse->getAsIncluded();
        $customerRelation = $customerResponse->getAsRelation();

        $this->assertIsArray($customerResponse->getAsData());
        $this->assertArrayHasKey('type', $customerData);
        $this->assertArrayHasKey('id', $customerData);
        $this->assertArrayHasKey('attributes', $customerData);
        $this->assertIsArray($customerResponse->getAsIncluded());
        $this->assertArrayHasKey('type', $customerIncluded);
        $this->assertArrayHasKey('id', $customerIncluded);
        $this->assertArrayHasKey('attributes', $customerIncluded);
        $this->assertIsArray($customerResponse->getAsRelation());
        $this->assertArrayHasKey('data', $customerRelation['customer']);
        $this->assertArrayHasKey('type', $customerRelation['customer']['data']);
        $this->assertArrayHasKey('id', $customerRelation['customer']['data']);
        Customer::destroy([$customer->first()->id]);
    }

    public function testProductResponse()
    {
        $customer = Customer::factory(1)->create();
        $product = Product::factory(1)->create([
            'customer_id' => $customer->first()->id
        ]);

        $productResponse = ProductResponse::createFromModel($product->first(), ['customer']);

        $this->log($productResponse);
        $productData = $productResponse->getAsData();
        $productIncluded = $productResponse->getAsIncluded();
        $productRelation = $productResponse->getAsRelation();

        $this->assertIsArray($productData);
        $this->assertArrayHasKey('type', $productData);
        $this->assertArrayHasKey('id', $productData);
        $this->assertArrayHasKey('attributes', $productData);
        $this->assertIsArray($productIncluded);
        $this->assertCount(1, $productIncluded['relationships']);
        $this->assertArrayHasKey('type', $productIncluded);
        $this->assertArrayHasKey('id', $productIncluded);
        $this->assertArrayHasKey('attributes', $productIncluded);
        $this->assertIsArray($productRelation);
        $this->assertCount(1, $productIncluded['relationships']);
        $this->assertArrayHasKey('data', $productRelation['product']);
        $this->assertArrayHasKey('type', $productRelation['product']['data']);
        $this->assertArrayHasKey('id', $productRelation['product']['data']);

        Product::destroy([$product->first()->id]);
        Customer::destroy([$customer->first()->id]);
    }

    public function testOrderItemResponse()
    {
        $customer = Customer::factory(1)->create();
        $product = Product::factory(1)->create([
            'customer_id' => $customer->first()->id
        ]);

        $order = Order::factory(1)->create([
            'customer_id' => $customer->first()->id
        ]);

        $orderItemCreated = OrderItem::factory(1)->create([
            'order_id' => $order->first()->id,
            'product_id' => $product->first()->id,
            'quantity' => 1
        ]);

        $OrderItemResponse = OrderItemResponse::createFromModel($orderItemCreated->first(), ['product']);

        $this->log($OrderItemResponse);
        $OrderItemData = $OrderItemResponse->getAsData();
        $orderItemIncluded = $OrderItemResponse->getAsIncluded();
        $orderItemRelation = $OrderItemResponse->getAsRelation();

        $this->assertIsArray($OrderItemData);
        $this->assertArrayHasKey('type', $OrderItemData);
        $this->assertArrayHasKey('id', $OrderItemData);
        $this->assertArrayHasKey('attributes', $OrderItemData);
        $this->assertIsArray($orderItemIncluded);
        $this->assertCount(1, $orderItemIncluded['relationships']);
        $this->assertArrayHasKey('type', $orderItemIncluded);
        $this->assertArrayHasKey('id', $orderItemIncluded);
        $this->assertArrayHasKey('attributes', $orderItemIncluded);
        $this->assertIsArray($orderItemRelation);
        $this->assertCount(1, $orderItemRelation['relationships']);
        $this->assertArrayHasKey('data', $orderItemRelation['order-item']);
        $this->assertArrayHasKey('type', $orderItemRelation['order-item']['data']);
        $this->assertArrayHasKey('id', $orderItemRelation['order-item']['data']);

        Product::destroy([$product->first()->id]);
        Customer::destroy([$customer->first()->id]);
        OrderItem::destroy([$orderItemCreated->first()->id]);
        Order::destroy([$order->first()->id]);
    }

    public function testOrderResponse()
    {
        $customer = Customer::factory(1)->create();
        $product = Product::factory(1)->create([
            'customer_id' => $customer->first()->id
        ]);

        $order = Order::factory(1)->create([
            'customer_id' => $customer->first()->id
        ]);

        $orderItem = OrderItem::factory(1)->create([
            'order_id' => $order->first()->id,
            'product_id' => $product->first()->id,
            'quantity' => 1
        ]);

        $OrderResponse = OrderResponse::createFromModel($order->first(), ['items', 'customer']);

        $orderResponseData = $OrderResponse->getAsData();
        $orderResponseIncluded = $OrderResponse->getAsIncluded();
        $orderResponseRelation = $OrderResponse->getAsRelation();

        $this->log($OrderResponse);
        $this->assertIsArray($orderResponseData);
        $this->assertArrayHasKey('type', $orderResponseData);
        $this->assertArrayHasKey('id', $orderResponseData);
        $this->assertArrayHasKey('attributes', $orderResponseData);
        $this->assertIsArray($orderResponseIncluded);
        $this->assertCount(2, $orderResponseIncluded);
        $this->assertIsArray($orderResponseIncluded);
        $this->assertIsArray($orderResponseRelation);
        $this->assertCount(2, $orderResponseRelation['relationships']);
        $this->assertArrayHasKey('data', $orderResponseRelation['order']);
        $this->assertArrayHasKey('type', $orderResponseRelation['order']['data']);
        $this->assertArrayHasKey('id', $orderResponseRelation['order']['data']);

        Product::destroy([$product->first()->id]);
        Customer::destroy([$customer->first()->id]);
        OrderItem::destroy([$orderItem->first()->id]);
        Order::destroy([$order->first()->id]);
    }

    public function testTransactionResponse()
    {

        $customer = Customer::factory(1)->create();
        $product = Product::factory(1)->create([
            'customer_id' => $customer->first()->id
        ]);

        $inventory = Inventory::factory(1)->create([
            'product_id' => $product->first()->id
        ]);

        $order = Order::factory(1)->create([
            'customer_id' => $customer->first()->id
        ]);

        $orderItem = OrderItem::factory(1)->create([
            'order_id' => $order->first()->id,
            'product_id' => $product->first()->id,
            'quantity' => 1
        ]);

        $transaction = Transaction::factory(1)->create([
            'quantity_request' => $orderItem->first()->quantity,
            'available_inventory' => $inventory->first()->available_inventory,
            'updated_available_inventory' => $inventory->first()->available_inventory - $orderItem->first()->quantity,
            'status_operation' => Order::READY_TO_SHIP_STATUS,
            'order_item_id' =>  $orderItem->first()->id
        ]);


        $transactionResponse = TransactionResponse::createFromModel($transaction->first(), ['items']);

        $transactionData = $transactionResponse->getAsData();
        $transactionIncluded = $transactionResponse->getAsIncluded();
        $transactionRelation = $transactionResponse->getAsRelation();

        $this->log($transactionResponse);
        $this->assertIsArray($transactionData);
        $this->assertArrayHasKey('type', $transactionData);
        $this->assertArrayHasKey('id', $transactionData);
        $this->assertArrayHasKey('attributes', $transactionData);
        $this->assertIsArray($transactionIncluded);
        $this->assertCount(1, $transactionIncluded);
        $this->assertIsArray($transactionIncluded);
        $this->assertIsArray($transactionRelation);
        $this->assertCount(1, $transactionRelation['relationships']);
        $this->assertArrayHasKey('data', $transactionRelation['order']);
        $this->assertArrayHasKey('type', $transactionRelation['order']['data']);
        $this->assertArrayHasKey('id', $transactionRelation['order']['data']);

    }

    public function testOrderRetrieveResponse()
    {
        $customer = Customer::factory(1)->create();
        $product = Product::factory(1)->create([
            'customer_id' => $customer->first()->id
        ]);

        $order = Order::factory(1)->create([
            'customer_id' => $customer->first()->id
        ]);

        $orderItem = OrderItem::factory(1)->create([
            'order_id' => $order->first()->id,
            'product_id' => $product->first()->id,
            'quantity' => 1
        ]);

        $OrderResponse = OrderResponse::createFromModel($order->first(), ['items']);
        $orderRetrieveResponse = new OrderRetrieveResponse($OrderResponse);

        $this->assertIsString(json_encode($orderRetrieveResponse));

        Product::destroy([$product->first()->id]);
        Customer::destroy([$customer->first()->id]);
        OrderItem::destroy([$orderItem->first()->id]);
        Order::destroy([$order->first()->id]);
    }

    public function testOrdersRetrieveResponse()
    {
        $customer = Customer::factory(1)->create();
        $product = Product::factory(1)->create([
            'customer_id' => $customer->first()->id
        ]);

        $order = Order::factory(1)->create([
            'customer_id' => $customer->first()->id
        ]);

        $orderItem = OrderItem::factory(1)->create([
            'order_id' => $order->first()->id,
            'product_id' => $product->first()->id,
            'quantity' => 1
        ]);

        $OrderResponse = OrderResponse::createFromModel($order->first(), ['items']);
        $orderRetrieveResponse = new OrderRetrieveResponse($OrderResponse);

        $customer2 = Customer::factory(1)->create();
        $product2 = Product::factory(1)->create([
            'customer_id' => $customer2->first()->id
        ]);

        $order2 = Order::factory(1)->create([
            'customer_id' => $customer2->first()->id
        ]);

        $orderItem2 = OrderItem::factory(1)->create([
            'order_id' => $order2->first()->id,
            'product_id' => $product2->first()->id,
            'quantity' => 1
        ]);

        $OrderResponse2 = OrderResponse::createFromModel($order2->first());
        $orderRetrieveResponse2 = new OrderRetrieveResponse($OrderResponse2);

        $this->assertIsString(json_encode($orderRetrieveResponse));
        $this->assertIsString(json_encode($orderRetrieveResponse2));

        Product::destroy([$product->first()->id, $product2->first()->id]);
        Customer::destroy([$customer->first()->id, $customer2->first()->id]);
        OrderItem::destroy([$orderItem->first()->id, $orderItem2->first()->id]);
        Order::destroy([$order->first()->id, $order2->first()->id]);
    }

    private function log($param): void
    {
        var_dump(json_encode($param->getAsData()));
        var_dump(json_encode($param->getAsIncluded()));
        var_dump(json_encode($param->getAsRelation()));
    }
}
