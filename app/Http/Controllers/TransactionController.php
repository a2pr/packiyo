<?php

namespace App\Http\Controllers;

use App\api\Responses\Objects\TransactionResponse;
use App\api\Responses\TransactionRetrieveResponse;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $include = request('included');
        $transactions = Transaction::all();
        $transactionResponses = [];

        foreach ($transactions as $transaction) {
            $transactionResponses[] = TransactionResponse::createFromModel($transaction, explode(",", $include));
        }

        $ordersRetrieveResponse = new TransactionRetrieveResponse($transactionResponses);
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
        //
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
