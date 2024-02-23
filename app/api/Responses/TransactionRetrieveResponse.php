<?php

namespace App\api\Responses;

class TransactionRetrieveResponse extends AbstractResponse
{
    public array $transactions;

    public function __construct(array $transactions)
    {
        $this->transactions = $transactions;
    }

    public function getData(): array
    {
        $data = [];
        foreach ($this->transactions as $transaction){
            $data[] = $transaction->getAsData();
        }

        return $data;
    }

    public function getIncluded(): array
    {
        $data = [];
        foreach ($this->transactions as $transaction){
            $data[] = $transaction->getAsIncluded();
        }

        return array_filter($data, function ($subArray) {
            return !empty($subArray);
        });
    }
}
