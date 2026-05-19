<?php

namespace App\Exceptions;

use RuntimeException;

class SkippedImportRowException extends RuntimeException
{
    public function __construct(public string $reason)
    {
        parent::__construct($reason);
    }
}
