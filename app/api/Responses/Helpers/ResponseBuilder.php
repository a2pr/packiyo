<?php

namespace App\api\Responses\Helpers;

class ResponseBuilder
{

    public static function buildData(
        string $type,
        int $id,
        array $attributes,
        array $relationships = []
    ): array
    {
        $result = [
            "type" => $type,
            "id" => $id,
            "attributes" => $attributes
        ];

        if (!empty($relationships)) {
            $result['relationships'] = $relationships;
        }

        return $result;
    }

    public static function buildRelationships(
        string $class_name,
        string $type,
        int $id,
        array $relationships = []
    ): array
    {
        $result = [
            $class_name => [
                "data" => [
                    "type" => $type,
                    "id" => $id
                ]
            ]
        ];

        if (!empty($relationships)) {
            $result['relationships'] = $relationships;
        }

        return $result;
    }

    public static function buildIncluded(
        string $type,
        int $id,
        array $attributes,
        array $relationships = []
    ): array
    {
        $result = self::buildData($type, $id, $attributes);

        if (!empty($relationships)) {
            $result['relationships'] = $relationships;
        }

        return $result;

    }
}
