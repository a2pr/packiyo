<?php

namespace App\api\Responses\Objects;

use App\api\Responses\Helpers\ResponseBuilder;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;

class ProductResponse extends AbstractResponse implements InterfaceResponse
{
    const INCLUDE_OPTIONS = [self::CUSTOMER_INCLUDED];
    const TYPE = 'products';
    public int $id;
    public ?CustomerResponse $customer;
    public string $name;
    public string $description;

    public function __construct(int $id, ?CustomerResponse $customer, string $name, string $description, string $created, string $updated, array $includedParams = [])
    {
        $this->id = $id;
        $this->customer = $customer;
        $this->name = $name;
        $this->description = $description;
        $this->created = $created;
        $this->updated = $updated;
        $this->includedParams = $includedParams;
    }


    public function getAsData(): array
    {
        $attributes = [
            "name" => $this->name,
            "description" => $this->description,
            "created" => $this->created,
            "updated" => $this->updated
        ];

        $relationships = [];
        if ($this->elementIsInIncludedParams(self::CUSTOMER_INCLUDED)) {
            $relationships[] = $this->customer->getAsRelation();
        }

        return ResponseBuilder::buildData(
            self::TYPE,
            $this->id,
            $attributes,
            $relationships
        );
    }

    public function getAsRelation(): array
    {
        if (!$this->hasIncludedParams()) {
            return [];
        }

        $relationships = [];
        if ($this->elementIsInIncludedParams(self::CUSTOMER_INCLUDED)) {
            $relationships[] = $this->customer->getAsRelation();
        }


        return ResponseBuilder::buildRelationships(
            'product',
            self::TYPE,
            $this->id,
            $relationships
        );
    }

    public function getAsIncluded(): array
    {
        if (!$this->hasIncludedParams()) {
            return [];
        }

        $included = [];
        if ($this->elementIsInIncludedParams(self::CUSTOMER_INCLUDED)) {
            $included[] = $this->customer->getAsIncluded();
        }

        $attributes = [
            "name" => $this->name,
            "description" => $this->description,
            "created" => $this->created,
            "updated" => $this->updated
        ];

        return ResponseBuilder::buildIncluded(
            self::TYPE,
            $this->id,
            $attributes,
            $included
        );
    }

    private function elementIsInIncludedParams(string $element): bool
    {
        $included = array_intersect(self::INCLUDE_OPTIONS, $this->includedParams);
        return in_array($element, $included);
    }

    public static function createFromModel(Product|Model $model, array $includedParams = []): self
    {
        $included = array_intersect(self::INCLUDE_OPTIONS, $includedParams);
        $customer = null;
        if (in_array(self::CUSTOMER_INCLUDED, $included)) {
            $customer = CustomerResponse::createFromModel($model->customer()->first(), $includedParams);
        }

        return new self(
            $model->id,
            $customer,
            $model->name,
            $model->description,
            $model->created_at,
            $model->updated_at,
            $includedParams
        );
    }
}
