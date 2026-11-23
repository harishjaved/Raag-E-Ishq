<?php

namespace App\Exceptions;

use Exception;

class OutOfStockException extends Exception
{
    protected int $available;

    public function __construct(string $message = 'The requested ticket quantity is no longer available.', int $available = 0, int $code = 422)
    {
        parent::__construct($message, $code);
        $this->available = $available;
    }

    public function getAvailable(): int
    {
        return $this->available;
    }
}
