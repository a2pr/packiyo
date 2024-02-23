<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Customer;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Transaction;
use Tests\TestCase;

class EndpointsTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function testGetRetrieveTransaction()
    {
        $orderId = $this->getOrderId();

        $response = $this->get("/api/retrieve-transaction/");

        $json = $response->json();
        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/vnd.api+json');
        $this->assertNotEmpty($json);
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
     * @return int
     */
    private function getOrderId(): int
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
            'order_item_id' => $orderItem->first()->id
        ]);

        return $order->first()->id;
    }
}
