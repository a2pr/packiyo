<?php

namespace App\api\Responses\Objects;

use App\api\Responses\Helpers\ResponseBuilder;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Model;

class CustomerResponse extends AbstractResponse implements InterfaceResponse
{
    const TYPE = 'customers';
    public int $id;
    public string $name;
    public string $location;

    public function __construct(int $id, string $name, string $location, string $created, string $updated, array $includedParams = [])
    {
        $this->id = $id;
        $this->name = $name;
        $this->location = $location;
        $this->created = $created;
        $this->updated = $updated;
        $this->includedParams = $includedParams;
    }

    public function getAsRelation(): array
    {
        return ResponseBuilder::buildRelationships(
            'customer',
            self::TYPE,
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
            self::TYPE,
            $this->id,
            $attributes
        );
    }

    public function getAsData(): array
    {
        $attributes = [
            "name" => $this->name,
            "location" => $this->location,
            "created" => $this->created,
            "updated" => $this->updated
        ];
        return ResponseBuilder::buildData(
            self::TYPE,
            $this->id,
            $attributes
        );
    }

    /**
     * @param Customer|Model $model
     * @param array $includedParams
     * @return self
     */
    public static function createFromModel(Customer|Model $model, array $includedParams = []):self
    {
        return new self(
            $model->id,
            $model->name,
            $model->location,
            $model->created_at,
            $model->updated_at,
            $includedParams
        );
    }
}
