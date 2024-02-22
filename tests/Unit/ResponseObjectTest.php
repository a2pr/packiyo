<?php

namespace Tests\Unit;

use App\api\Responses\Objects\CustomerResponse;
use App\api\Responses\Objects\OrderItemResponse;
use App\api\Responses\Objects\OrderResponse;
use App\api\Responses\Objects\ProductResponse;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Tests\TestCase;

class ResponseObjectTest extends TestCase
{
    public function testCustomerResponse()
    {
        $customer = Customer::factory(1)->create();
        $customerResponse = CustomerResponse::createFromModel($customer->first());

        $this->assertIsArray($customerResponse->getAsData());
        $this->assertIsArray($customerResponse->getAsIncluded());
        $this->assertIsArray($customerResponse->getAsRelation());
        Customer::destroy([$customer->first()->id]);
    }

    public function testProductResponse()
    {
        $customer = Customer::factory(1)->create();
        $product = Product::factory(1)->create([
            'customer_id' => $customer->first()->id
        ]);

        $productResponse = ProductResponse::createFromModel($product->first());

        $this->log($productResponse);
        $this->assertIsArray($productResponse->getAsData());
        $this->assertIsArray($productResponse->getAsIncluded());
        $this->assertIsArray($productResponse->getAsRelation());

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
            'order_id'=> $order->first()->id,
            'product_id'=> $product->first()->id,
            'quantity' => 1
        ]);

        $OrderItemResponse = OrderItemResponse::createFromModel($orderItemCreated->first());

        $this->log($OrderItemResponse);
        $this->assertIsArray($OrderItemResponse->getAsData());
        $this->assertIsArray($OrderItemResponse->getAsIncluded());
        $this->assertIsArray($OrderItemResponse->getAsRelation());

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
            'order_id'=> $order->first()->id,
            'product_id'=> $product->first()->id,
            'quantity' => 1
        ]);

        $OrderResponse = OrderResponse::createFromModel($order->first());

        $this->log($OrderResponse);
        $this->assertIsArray($OrderResponse->getAsData());
        $this->assertIsArray($OrderResponse->getAsIncluded());
        $this->assertIsArray($OrderResponse->getAsRelation());

        Product::destroy([$product->first()->id]);
        Customer::destroy([$customer->first()->id]);
        OrderItem::destroy([$orderItem->first()->id]);
        Order::destroy([$order->first()->id]);
    }

    private function log($param): void
    {
        var_dump(json_encode($param->getAsData()));
        var_dump(json_encode($param->getAsIncluded()));
        var_dump(json_encode($param->getAsRelation()));
    }
}
