<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Transaction;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{

    public function setUp(): void
    {
        parent::setUp();
        Order::truncate();
        OrderItem::truncate();
        Inventory::truncate();
        Customer::truncate();
        Transaction::truncate();
        Product::truncate();
    }

    public function testGetRetrieveOrder()
    {
        $orderId = $this->getOrderId();

        $response = $this->get("/api/retrieve-order/$orderId");

        $json = $response->json();
        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/vnd.api+json');
        $this->assertNotEmpty($json);
    }

    public function testGetRetrieveOrdersWithIncludedParams()
    {
        $orderId = $this->getOrderId();

        $includeParams = '?included=items,customer';
        $response = $this->get("/api/retrieve-order/$includeParams");

        $json = $response->json();
        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/vnd.api+json');

        $this->assertNotEmpty($json);
        $this->assertArrayHasKey('data', $json);
        $this->assertTrue(count($json['data']) > 0);
        $this->assertArrayHasKey('type', $json['data'][0]);
        $this->assertArrayHasKey('id', $json['data'][0]);
        $this->assertArrayHasKey('attributes', $json['data'][0]);
        $this->assertArrayHasKey('relationships', $json['data'][0]);
        $this->assertArrayHasKey('included', $json);
        $this->assertTrue(count($json['included']) > 0);
    }

    public function testGetRetrieveOrderWithIncludedParams()
    {
        $orderId = $this->getOrderId();

        $includeParams = '?included=items,customer';
        $response = $this->get("/api/retrieve-order/$orderId/$includeParams");

        $json = $response->json();
        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/vnd.api+json');

        $this->assertNotEmpty($json);
        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('type', $json['data']);
        $this->assertArrayHasKey('id', $json['data']);
        $this->assertArrayHasKey('attributes', $json['data']);
        $this->assertArrayHasKey('relationships', $json['data']);
        $this->assertArrayHasKey('included', $json);
        $this->assertCount(2, $json['included']);
    }

    public function testGetRetrieveOrders()
    {
        $orderId = $this->getOrderId();

        $response = $this->get("/api/retrieve-order/");

        $json = $response->json();
        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/vnd.api+json');
        $this->assertNotEmpty($json);
    }

    public function testPostCreateOrders()
    {
        [$customerId, $productId] = $this->getCustomerAndProductIds();
        $data = [
            'customer_id' => $customerId,
            'products' => [
                ['id' => $productId, 'quantity' => 1]
            ]
        ];

        $response = $this->postJson('/api/create-order', $data);

        $json = $response->json();
        $response->assertStatus(201) // Assuming a successful resource creation returns 201 status
        ->assertHeader('Content-Type', 'application/vnd.api+json');
        $this->assertNotEmpty($json);
        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('type', $json['data']);
        $this->assertArrayHasKey('id', $json['data']);
        $this->assertArrayHasKey('attributes', $json['data']);
        $this->assertArrayHasKey('included', $json);


        $orders = Order::join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->where('order_items.product_id', $productId)
            ->where('orders.customer_id', $customerId)
            ->select('orders.*', 'order_items.product_id')
            ->get();

        $this->assertEquals(1, $orders->count());

        $this->assertEquals(Order::READY_TO_SHIP_STATUS, $orders->first()->status);
    }

    public function testPostCreateOrdersPending()
    {
        [$customerId, $productId] = $this->getCustomerAndProductIds();
        $data = [
            'customer_id' => $customerId,
            'products' => [
                ['id' => $productId, 'quantity' => 1000]
            ]
        ];

        $response = $this->postJson('/api/create-order', $data);

        $json = $response->json();
        $response->assertStatus(201) // Assuming a successful resource creation returns 201 status
        ->assertHeader('Content-Type', 'application/vnd.api+json');
        $this->assertNotEmpty($json);
        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('type', $json['data']);
        $this->assertArrayHasKey('id', $json['data']);
        $this->assertArrayHasKey('attributes', $json['data']);
        $this->assertArrayHasKey('included', $json);


        $order = Order::join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->where('order_items.product_id', $productId)
            ->where('orders.customer_id', $customerId)
            ->select('orders.*', 'order_items.product_id')
            ->first();

        $this->assertEquals(Order::PENDING_STATUS, $order->status);
    }


    private function getCustomerAndProductIds(): array
    {
        $customer = Customer::factory(1)->create();
        $product = Product::factory(1)->create([
            'customer_id' => $customer->first()->id
        ]);

        $inventory = Inventory::factory(1)->create([
            'product_id' => $product->first()->id
        ]);

        return [
            $customer->first()->id,
            $product->first()->id
        ];
    }

    /**
     * @param int $param
     * @return int
     */
    private function getOrderId( int $param = 1): int
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
            'quantity' => $param
        ]);

        $transaction = Transaction::factory(1)->create([
            'quantity_request' => $orderItem->first()->quantity,
            'available_inventory' => $inventory->first()->available_inventory,
            'updated_available_inventory' => $inventory->first()->available_inventory - $orderItem->first()->quantity,
            'status_operation' => Order::READY_TO_SHIP_STATUS,
            'order_item_id' => $orderItem->first()->id
        ]);

        return $order->first()->id;
    }
}
