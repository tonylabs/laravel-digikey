<?php

namespace TONYLABS\DigiKey\Exceptions;

class DigiKeyApiException extends DigiKeyException
{
    protected int $statusCode;
    protected array $errorDetails;

    public function __construct(string $message, int $statusCode = 0, array $errorDetails = [], \Throwable $previous = null)
    {
        parent::__construct($message, $statusCode, $previous);
        $this->statusCode = $statusCode;
        $this->errorDetails = $errorDetails;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getErrorDetails(): array
    {
        return $this->errorDetails;
    }
}