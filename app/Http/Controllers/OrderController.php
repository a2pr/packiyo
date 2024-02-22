<?php

namespace App\Http\Controllers;

use App\api\Responses\ErrorResponse;
use App\api\Responses\Objects\OrderResponse;
use App\api\Responses\OrderCreateResponse;
use App\api\Responses\OrderRetrieveResponse;
use App\api\Responses\OrdersRetrieveResponse;
use App\DataTransferObject\OrderInventoryDto;
use App\Events\OrderEvent;
use App\Http\Facades\OrderFacade;
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
        $orders = Order::all();

        $orderResponses = [];

        foreach ($orders as $order) {
            $orderResponses[] = OrderResponse::createFromModel($order);
        }

        $ordersRetrieveResponse = new OrdersRetrieveResponse($orderResponses);
        $headers = [
            'Content-Type' => 'application/vnd.api+json'
        ];

        return response()->json($ordersRetrieveResponse, ResponseAlias::HTTP_OK, $headers);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required',
            'products' => 'required|array'
        ]);

        $data = $request->all();
        // check elements in product array
        $orderFacade = new OrderFacade();

        list($statusCode, $orderResponse) = $orderFacade->processRequest($data);
        $headers = [
            'Content-Type' => 'application/vnd.api+json'
        ];

        return response()->json($orderResponse, $statusCode, $headers);
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        $orderResponse = new OrderRetrieveResponse(OrderResponse::createFromModel($order));

        $headers = [
            'Content-Type' => 'application/vnd.api+json'
        ];

        return response()->json($orderResponse, ResponseAlias::HTTP_OK, $headers);
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
