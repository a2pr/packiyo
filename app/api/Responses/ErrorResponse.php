<?php

namespace App\api\Responses;

use App\api\Responses\Helpers\ResponseBuilder;

class ErrorResponse extends AbstractResponse
{
    public int $status;
    public \Exception $exception;

    public function __construct(int $status, \Exception $exception)
    {
        $this->status = $status;
        $this->exception = $exception;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'error' => $this->getErrors()
        ];
    }

    public function getErrors(): array
    {
        return ResponseBuilder::buildError(
            $this->status,
            $this->exception->getFile(),
            $this->exception::class,
            $this->exception->getMessage(),
        );
    }
}
