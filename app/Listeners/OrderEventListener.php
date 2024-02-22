<?php

namespace App\Listeners;

use App\Events\OrderEvent;
use App\Models\Transaction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class OrderEventListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OrderEvent $event): void
    {
        $orderInventoryDtos = $event->orderInventoryDtos;
        foreach ($orderInventoryDtos as $element){

            $transaction = new Transaction();
            $transaction->order_item_id = $element->getOrderItemId();
            $transaction->quantity_request = $element->getQuantityRequest();
            $transaction->available_inventory = $element->getAvailableQuantity();
            $transaction->updated_available_inventory = $element->getUpdatedAvailableQuantity();
            $transaction->status_operation = $element->isStatus();
            $transaction->save();
        }

    }
}
