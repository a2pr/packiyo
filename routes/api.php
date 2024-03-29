<?php

use App\api\Responses\OrderRetrieveResponse;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\TransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('hi', function () {
    $orderResponse = new OrderRetrieveResponse(1, [], 1);
    return response()->json($orderResponse);
});

Route::post('create-order', [OrderController::class, 'store'])->name('order.store');
Route::get('retrieve-order', [OrderController::class, 'index'])->name('order.index');
Route::get('retrieve-order/{order}', [OrderController::class, 'show'])->name('order.show');
Route::get('retrieve-transaction', [TransactionController::class, 'index'])->name('transaction.index');
