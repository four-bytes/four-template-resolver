<?php

declare(strict_types=1);

namespace Four\TemplateResolver\Configuration;

/**
 * Configuration for template resolver
 *
 * Manages template directory, caching, and processing options.
 */
class TemplateConfiguration
{
    /**
     * @param string $templateDirectory Path to template directory
     * @param bool $enableCaching Enable template caching
     * @param string $templateExtension File extension for templates
     * @param bool $strictMode Throw exceptions on missing templates
     * @param LanguageMapping $languageMapping Country to language mapping
     */
    public function __construct(
        public readonly string $templateDirectory,
        public readonly bool $enableCaching = true,
        public readonly string $templateExtension = '.txt',
        public readonly bool $strictMode = false,
        public readonly LanguageMapping $languageMapping = new LanguageMapping(),
    ) {
    }

    /**
     * Create configuration with template directory
     */
    public static function withDirectory(string $templateDirectory): self
    {
        return new self($templateDirectory);
    }

    /**
     * Create configuration with strict mode enabled
     */
    public static function withStrictMode(string $templateDirectory): self
    {
        return new self(
            templateDirectory: $templateDirectory,
            strictMode: true
        );
    }

    /**
     * Create configuration with caching disabled
     */
    public static function withoutCaching(string $templateDirectory): self
    {
        return new self(
            templateDirectory: $templateDirectory,
            enableCaching: false
        );
    }

    /**
     * Create configuration with custom language mapping
     */
    public static function withLanguageMapping(string $templateDirectory, LanguageMapping $languageMapping): self
    {
        return new self(
            templateDirectory: $templateDirectory,
            languageMapping: $languageMapping
        );
    }
}
