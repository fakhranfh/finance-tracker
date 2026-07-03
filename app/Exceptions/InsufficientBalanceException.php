<?php

namespace App\Exceptions;

use RuntimeException;

class InsufficientBalanceException extends RuntimeException
{
    public function __construct(string $message = 'Wallet balance is insufficient for this transaction.')
    {
        parent::__construct($message);
    }
}
