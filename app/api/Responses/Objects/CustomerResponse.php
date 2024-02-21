<?php

namespace App\api\Responses\Objects;

use App\api\Responses\Helpers\ResponseBuilder;

class CustomerResponse implements InterfaceResponse
{
    public int $id;
    public string $name;
    public string $location;
    public string $created;
    public string $updated;

    public function __construct(int $id, string $name, string $location, string $created, string $updated)
    {
        $this->id = $id;
        $this->name = $name;
        $this->location = $location;
        $this->created = $created;
        $this->updated = $updated;
    }

    public function getAsRelation(): array
    {
        return ResponseBuilder::buildRelationships(
            'customer',
            'customers',
            $this->id,
        );
    }

    public function getAsIncluded(): array
    {
        $attributes = [
            "name" => $this->name,
            "location" => $this->location,
            "created" => $this->created,
            "updated" => $this->updated
        ];

        return ResponseBuilder::buildIncluded(
            'customers',
            $this->id,
            $attributes
        );
    }
}
