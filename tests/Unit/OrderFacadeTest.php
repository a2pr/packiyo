<?php

namespace Tests\Unit;

use App\api\Responses\ErrorResponse;
use App\api\Responses\Objects\CustomerResponse;
use App\api\Responses\Objects\OrderResponse;
use App\api\Responses\OrderCreateResponse;
use App\Http\Facades\OrderFacade;
use App\Models\Customer;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Transaction;
use Tests\TestCase;

class OrderFacadeTest extends TestCase
{

    /**
     * @return void
     */
    public function testProcessRequest()
    {
        [$customer_id, $product_id] = self::getCustomerAndProductIds();

        $data = [
            'customer_id' => $customer_id,
            'products' => [
                ['id' => $product_id, 'quantity' => 1],
            ]
        ];
        $orderFacade = new OrderFacade();
        $result = $orderFacade->processRequest($data);
        $order_id = $result[1]->order->order_id;
        $this->assertEquals(201, $result[0]);
        $this->assertInstanceOf(OrderCreateResponse::class, $result[1]);
        $orderCount = Order::find($order_id)->count();

        $orderItems = OrderItem::where('order_id', $order_id);
        $orderItemsCount = $orderItems->count();
        $transactionCount = Transaction::where('order_item_id', $orderItems->first()->id)->count();

        $this->assertEquals(1, $orderCount);
        $this->assertEquals(1, $orderItemsCount);
        $this->assertEquals(1, $transactionCount);
    }

    /**
     * @return void
     */
    public function testProcessRequestMultipleProducts()
    {
        [$customer_id, $product_id] = self::getCustomerAndProductIds();
        $customer = Customer::find($customer_id)->first();
        [$customer_id, $product_id_two] = self::getCustomerAndProductIds($customer);
        $data = [
            'customer_id' => $customer_id,
            'products' => [
                ['id' => $product_id, 'quantity' => 1],
                ['id' => $product_id_two, 'quantity' => 1],
            ]
        ];
        $orderFacade = new OrderFacade();
        $result = $orderFacade->processRequest($data);
        $order_id = $result[1]->order->order_id;
        $this->assertEquals(201, $result[0]);
        $this->assertInstanceOf(OrderCreateResponse::class, $result[1]);
        $orderCount = Order::find($order_id)->count();

        $orderItems = OrderItem::where('order_id', $order_id);
        $orderItemsCount = $orderItems->count();
        $transactionCount = 0;
        foreach ($orderItems->get() as $item) {

            $transactionCount += Transaction::where('order_item_id', $item->id)->count();
        }

        $this->assertEquals(1, $orderCount);
        $this->assertEquals(2, $orderItemsCount);
        $this->assertEquals(2, $transactionCount);
    }

    /**
     * @return void
     */
    public function testProcessRequestMultipleProductsSameProduct()
    {
        [$customer_id, $product_id] = self::getCustomerAndProductIds();
        $customer = Customer::find($customer_id)->first();
        [$customer_id, $product_id_two] = self::getCustomerAndProductIds($customer);
        $data = [
            'customer_id' => $customer_id,
            'products' => [
                ['id' => $product_id, 'quantity' => 1],
                ['id' => $product_id_two, 'quantity' => 1],
                ['id' => $product_id, 'quantity' => 1],
            ]
        ];
        $orderFacade = new OrderFacade();
        $result = $orderFacade->processRequest($data);
        $order_id = $result[1]->order->order_id;
        $this->assertEquals(201, $result[0]);
        $this->assertInstanceOf(OrderCreateResponse::class, $result[1]);
        $orderCount = Order::find($order_id)->count();

        $orderItems = OrderItem::where('order_id', $order_id);
        $orderItemsCount = $orderItems->count();
        $transactionCount = 0;
        foreach ($orderItems->get() as $item) {

            $transactionCount += Transaction::where('order_item_id', $item->id)->count();
        }

        $this->assertEquals(1, $orderCount);
        $this->assertEquals(2, $orderItemsCount);
        $this->assertEquals(2, $transactionCount);
    }

    /**
     * @return void
     */
    public function testProcessRequestExceptionInvalidProduct()
    {
        [$customer_id, $product_id] = self::getCustomerAndProductIds();
        $data = [
            'customer_id' => $customer_id,
            'products' => [
                ['id' => 999999, 'quantity' => 1]
            ]
        ];
        $orderFacade = new OrderFacade();
        $result = $orderFacade->processRequest($data);

        $this->assertEquals(400, $result[0]);
        $this->assertInstanceOf(ErrorResponse::class, $result[1]);
    }

    /**
     * @return void
     */
    public function testProcessRequestExceptionInvalidCustomer()
    {
        [$customer_id, $product_id] = self::getCustomerAndProductIds();
        $data = [
            'customer_id' => 999999,
            'products' => [
                ['id' => $product_id, 'quantity' => 1]
            ]
        ];
        $orderFacade = new OrderFacade();
        $result = $orderFacade->processRequest($data);

        $this->assertEquals(400, $result[0]);
        $this->assertInstanceOf(ErrorResponse::class, $result[1]);
    }


    public static function getCustomerAndProductIds($customer = null): array
    {
        if (empty($customer)) {
            $customer = Customer::factory(1)->create();
        }
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
     * @dataProvider getDataProvider
     * @return void
     */
    public function testGroupProductDetailsInRequest($data, $expectedResult)
    {
        $orderFacade = new OrderFacade();
        $result = $orderFacade->groupProductDetailsInRequest($data);
        $this->assertEquals($expectedResult,$expectedResult);
    }

    public static function getDataProvider(): array
    {
        return [
            [
                [
                    'customer_id' => 1,
                    'products' => [
                        ['id' => 1, 'quantity' => 1]
                    ]
                ],
                [
                    'customer_id' => 1,
                    'products' => [
                        ['id' => 1, 'quantity' => 1]
                    ]
                ]
            ],
            [
                [
                    'customer_id' => 1,
                    'products' => [
                        ['id' => 1, 'quantity' => 1],
                        ['id' => 2, 'quantity' => 1],
                    ]
                ],
                [
                    'customer_id' => 1,
                    'products' => [
                        ['id' => 1, 'quantity' => 1],
                        ['id' => 2, 'quantity' => 1],
                    ]
                ]
            ],
            [

                [
                    'customer_id' => 1,
                    'products' => [
                        ['id' => 1, 'quantity' => 1],
                        ['id' => 2, 'quantity' => 1],
                        ['id' => 1, 'quantity' => 1],
                    ]
                ],
                [
                    'customer_id' => 1,
                    'products' => [
                        ['id' => 1, 'quantity' => 2],
                        ['id' => 2, 'quantity' => 1],
                    ]
                ]
            ],
            [

                [
                    'customer_id' => 1,
                    'products' => [
                        ['id' => 1, 'quantity' => 1],
                        ['id' => 2, 'quantity' => 1],
                        ['id' => 2, 'quantity' => 1],
                        ['id' => 3, 'quantity' => 1],
                    ]
                ],
                [
                    'customer_id' => 1,
                    'products' => [
                        ['id' => 1, 'quantity' => 1],
                        ['id' => 2, 'quantity' => 2],
                        ['id' => 3, 'quantity' => 1],
                    ]
                ]
            ],
        ];
    }
}
