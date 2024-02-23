<?php

namespace App\Http\Controllers;

use App\api\Responses\ErrorResponse;
use App\api\Responses\Objects\OrderResponse;
use App\api\Responses\OrderRetrieveResponse;
use App\api\Responses\OrdersRetrieveResponse;
use App\Http\Facades\OrderFacade;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class OrderController extends Controller
{
    const POST_RULE = [
        'customer_id' => 'required|integer|min:1',
        'products' => 'required|array',
        'products.*.id' => 'required|integer|min:1',
        'products.*.quantity' => 'required|integer|min:1',
    ];
    const POST_MESSAGES = [
        'customer_id.required' => 'The :attribute field is required.',
        'products.required' => 'The :attribute field is required.',
        'products' => 'invalid product list',
        'products.*' => 'Missing :attribute in products list',
        'products.*.quantity' => 'Invalid :attribute ',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $include = request('included');
        $orders = Order::all();
        $orderResponses = [];

        foreach ($orders as $order) {
            $orderResponses[] = OrderResponse::createFromModel($order, explode(",", $include));
        }

        $ordersRetrieveResponse = new OrdersRetrieveResponse($orderResponses);
        return response()->json($ordersRetrieveResponse, ResponseAlias::HTTP_OK, self::getHeaders());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),
            self::POST_RULE,
            self::POST_MESSAGES
        );

        if ($validator->fails()) {
            $exception = new \Exception(
                $validator->errors(),
                ResponseAlias::HTTP_BAD_REQUEST);
            $errorResponse = new ErrorResponse(
                $exception->getCode(),
                $exception
            );

            return response()->json($errorResponse, 400, self::getHeaders());
        }

        $data = $validator->validated();
        // check elements in product array
        $orderFacade = new OrderFacade();

        list($statusCode, $orderResponse) = $orderFacade->processRequest($data);

        return response()->json($orderResponse, $statusCode, self::getHeaders());
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        $include = request('included');
        $orderResponse = new OrderRetrieveResponse(OrderResponse::createFromModel($order, explode(",", $include)));
        return response()->json($orderResponse, ResponseAlias::HTTP_OK, self::getHeaders());
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

    public static function getHeaders(): array
    {
        return [
            'Content-Type' => 'application/vnd.api+json'
        ];
    }
}
