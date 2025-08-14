<?php

declare(strict_types=1);

namespace Four\TemplateResolver;

/**
 * Interface for template resolver implementations
 *
 * Provides a contract for template resolution with entity processing,
 * hierarchical fallback, and multi-language support.
 */
interface TemplateResolverInterface
{
    /**
     * Resolve template content from one or more entities
     *
     * @param string $templateName Template name (without .txt extension)
     * @param object|object[] $entities Single entity or array of entities
     * @param string|null $context Optional context for hierarchical fallback (e.g., 'amazon', 'ebay')
     * @param string|null $language Optional language override
     * @return string Processed template content
     * @throws Exception\TemplateNotFoundException When template is not found
     * @throws Exception\InvalidTemplateException When template syntax is invalid
     * @throws Exception\EntityExtractionException When entity data extraction fails
     */
    public function resolveFromEntity(string $templateName, object|array $entities, ?string $context = null, ?string $language = null): string;

    /**
     * Resolve template content from data array
     *
     * @param string $templateName Template name (without .txt extension)
     * @param array<string, mixed> $data Template variables
     * @param string|null $context Optional context for hierarchical fallback
     * @return string Processed template content
     * @throws Exception\TemplateNotFoundException When template is not found
     * @throws Exception\InvalidTemplateException When template syntax is invalid
     */
    public function resolve(string $templateName, array $data, ?string $context = null): string;

    /**
     * Check if a template exists
     *
     * @param string $templateName Template name
     * @param string|null $context Optional context
     * @return bool True if template exists
     */
    public function hasTemplate(string $templateName, ?string $context = null): bool;

    /**
     * Get all available templates for a context
     *
     * @param string|null $context Optional context filter
     * @return string[] Array of template names
     */
    public function getAvailableTemplates(?string $context = null): array;

    /**
     * Clear template cache
     *
     * @return void
     */
    public function clearCache(): void;

    /**
     * Register template content programmatically
     *
     * @param string $templateName Template name
     * @param string $content Template content
     * @param string|null $context Optional context
     * @return void
     */
    public function registerTemplate(string $templateName, string $content, ?string $context = null): void;

    /**
     * Get cache statistics
     *
     * @return array Cache statistics
     */
    public function getCacheStats(): array;
}
