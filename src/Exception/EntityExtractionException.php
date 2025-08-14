<?php

declare(strict_types=1);

namespace Four\TemplateResolver\Exception;

use RuntimeException;

/**
 * Exception thrown when entity data extraction fails
 */
class EntityExtractionException extends RuntimeException
{
    public function __construct(string $entityClass, string $reason, int $code = 0, ?\Throwable $previous = null)
    {
        $message = "Failed to extract data from entity '{$entityClass}': {$reason}";

        parent::__construct($message, $code, $previous);
    }
}
