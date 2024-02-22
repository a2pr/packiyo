<?php

namespace App\Http\Controllers;

use App\api\Responses\ErrorResponse;
use App\api\Responses\Objects\OrderResponse;
use App\api\Responses\OrderCreateResponse;
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

                $orderItem = new OrderItem();
                $orderItem->quantity = $element['quantity'];
                $orderItem->product_id = $element['id'];
                $orderItems[] = $orderItem;
            }

            $order->orderItems()->saveMany($orderItems);

            $order->load('orderItems');
            //event Transaction

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
