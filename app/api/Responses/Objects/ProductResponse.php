<?php

namespace App\api\Responses\Objects;

use App\api\Responses\Helpers\ResponseBuilder;
use App\Models\Customer;

class ProductResponse implements InterfaceResponse
{
    public int $id;
    public CustomerResponse $customer;
    public string $name;
    public string $description;
    public string $created;
    public string $updated;

    public function __construct(int $id, CustomerResponse $customer, string $name, string $description, string $created, string $updated)
    {
        $this->id = $id;
        $this->customer = $customer;
        $this->name = $name;
        $this->description = $description;
        $this->created = $created;
        $this->updated = $updated;
    }


    public function getAsIncluded(): array
    {
        return ResponseBuilder::buildRelationships(
            'product',
            'products',
            $this->id,
            $this->customer->getAsRelation()
        );
    }

    public function getAsRelation(): array
    {
        $attributes = [
            "name" => $this->name,
            "description" => $this->description,
            "created" => $this->created,
            "updated" => $this->updated
        ];

        return ResponseBuilder::buildIncluded(
            'customers',
            $this->id,
            $attributes,
            $this->customer->getAsRelation()
        );
    }
}
