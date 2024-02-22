<?php

namespace App\Http\Controllers;

use App\api\Responses\ErrorResponse;
use App\api\Responses\Objects\OrderResponse;
use App\api\Responses\OrderCreateResponse;
use App\DataTransferObject\OrderInventoryDto;
use App\Events\OrderEvent;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required',
            'products' => 'required'
        ]);

        $data = $request->all();
        // check elements in product array
        $statusCode = ResponseAlias::HTTP_OK;
        try {
            $customer = Customer::find($data['customer_id']);

            if (empty($customer)) {
                throw new \Exception(
                    "Customer not found",
                    ResponseAlias::HTTP_BAD_REQUEST
                );
            }

            //create order
            $order = new Order();
            $order->status = Order::PENDING_STATUS;
            $order->customer_id = $data['customer_id'];
            $order->save();

            $newStatus = Order::PENDING_STATUS;
            //create order items
            $orderItems = [];
            foreach ($data['products'] as $element) {

                $product = Product::find($element['id']);
                if (empty($product)) {
                    throw new \Exception(
                        "Product not found",
                        ResponseAlias::HTTP_BAD_REQUEST
                    );
                }

                $inventory = $product->inventory()->first();
                $orderItem = new OrderItem();
                $orderItem->quantity = $element['quantity'];
                $orderItem->product_id = $element['id'];

                $orderItems[] = $orderItem;
                $updated_available_inventory = $inventory->available_inventory - $element['quantity'];
                $element['status_operation'] = $updated_available_inventory > 0;
            }

            $updatedInventory = true;
            foreach ($data['products'] as $element) {
                if (!$element['status_operation']) {
                    $updatedInventory = false;
                    break;
                }
            }
            if ($updatedInventory) {
                $newStatus = Order::READY_TO_SHIP_STATUS;
                foreach ($data['products'] as $element) {

                    $product = Product::find($element['id']);
                    $inventory = $product->inventory()->first();
                    $updated_available_inventory = $inventory->available_inventory - $element['quantity'];
                    $inventory->update(['available_inventory' => $updated_available_inventory]);
                }
            } else {
                $newStatus = Order::PENDING_STATUS;
            }

            $order->orderItems()->saveMany($orderItems);

            $order->load('orderItems');
            if ($newStatus != Order::PENDING_STATUS) {
                $order->update(['status' => Order::PENDING_STATUS]);
            }

            //event Transaction
            $orderInventoryDtos = [];
            foreach ($order->orderItems()->get() as $orderItem) {

                $product = $orderItem->products()->first();
                $inventory = $product->inventory()->first();

                $orderInventoryDtos[] = new OrderInventoryDto(
                    $inventory->available_inventory,
                    $inventory->available_inventory - $orderItem->quantity,
                    $orderItem->quantity,
                    $orderItem->id,
                    $newStatus
                );
            }

            OrderEvent::dispatch($orderInventoryDtos);
            $orderResponse = new OrderCreateResponse(OrderResponse::createFromModel($order));

        } catch (\Exception $e) {
            $statusCode = $e->getCode();
            $orderResponse = new ErrorResponse(
                $e->getCode(),
                $e
            );
        }
        $headers = [
            'Content-Type' => 'application/vnd.api+json'
        ];

        return response()->json($orderResponse, $statusCode, $headers);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
