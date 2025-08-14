<?php

declare(strict_types=1);

namespace Four\TemplateResolver\Exception;

use RuntimeException;

/**
 * Exception thrown when a requested template cannot be found
 */
class TemplateNotFoundException extends RuntimeException
{
    public function __construct(string $templateName, ?string $context = null, int $code = 0, ?\Throwable $previous = null)
    {
        $message = $context !== null
            ? "Template '{$templateName}' not found for context '{$context}'"
            : "Template '{$templateName}' not found";

        parent::__construct($message, $code, $previous);
    }
}
