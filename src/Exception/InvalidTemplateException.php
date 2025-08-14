<?php

declare(strict_types=1);

namespace Four\TemplateResolver\Exception;

use RuntimeException;

/**
 * Exception thrown when template content contains invalid syntax
 */
class InvalidTemplateException extends RuntimeException
{
    public function __construct(string $templateName, string $reason, int $code = 0, ?\Throwable $previous = null)
    {
        $message = "Invalid template '{$templateName}': {$reason}";

        parent::__construct($message, $code, $previous);
    }
}
