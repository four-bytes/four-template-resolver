<?php

declare(strict_types=1);

namespace Four\TemplateResolver;

use Four\TemplateResolver\Configuration\TemplateConfiguration;
use Four\TemplateResolver\Exception\InvalidTemplateException;
use Four\TemplateResolver\Exception\TemplateNotFoundException;

/**
 * Modern template resolver with entity processing and hierarchical fallback
 *
 * Features:
 * - Entity-based template processing using reflection
 * - Hierarchical template fallback (context_template.txt -> template.txt)
 * - Multi-language support with automatic detection
 * - Advanced template syntax (variables, conditionals, loops)
 * - Performance-optimized with caching
 * - Configurable and extensible
 */
class TemplateResolver implements TemplateResolverInterface
{
    private TemplateCache $cache;
    private EntityDataExtractor $entityExtractor;
    private LanguageDetector $languageDetector;

    public function __construct(
        private readonly TemplateConfiguration $configuration
    ) {
        $this->cache = new TemplateCache($this->configuration->enableCaching);
        $this->entityExtractor = new EntityDataExtractor($this->configuration->enableCaching);
        $this->languageDetector = new LanguageDetector($this->configuration->languageMapping);
    }

    public function resolveFromEntity(string $templateName, object|array $entities, ?string $context = null, ?string $language = null): string
    {
        // Ensure entities is always an array
        $entityArray = is_array($entities) ? $entities : [$entities];

        // Extract data from entities
        $data = $this->entityExtractor->extractMultiple($entityArray);

        // Auto-detect language if not provided
        if ($language === null) {
            $language = $this->languageDetector->detectFromEntities($entityArray);
        } else {
            $language = $this->languageDetector->normalizeLanguage($language);
        }

        // Add language to template name for language-specific templates
        $languageTemplateName = "{$templateName}_{$language}";

        // Try to resolve with language suffix first
        if ($this->hasTemplate($languageTemplateName, $context)) {
            return $this->resolve($languageTemplateName, $data, $context);
        }

        // Fall back to template without language suffix
        return $this->resolve($templateName, $data, $context);
    }

    public function resolve(string $templateName, array $data, ?string $context = null): string
    {
        $template = $this->loadTemplate($templateName, $context);
        return $this->processTemplate($template, $data);
    }

    public function hasTemplate(string $templateName, ?string $context = null): bool
    {
        $paths = $this->getTemplatePaths($templateName, $context);

        foreach ($paths as $path) {
            if (file_exists($path)) {
                return true;
            }
        }

        return false;
    }

    public function getAvailableTemplates(?string $context = null): array
    {
        $templates = [];
        $pattern = $this->configuration->templateDirectory . '/*' . $this->configuration->templateExtension;

        foreach (glob($pattern) ?: [] as $file) {
            $basename = basename($file, $this->configuration->templateExtension);

            if ($context !== null) {
                // Filter by context
                if (str_starts_with($basename, $context . '_')) {
                    $templates[] = substr($basename, strlen($context) + 1);
                }
            } else {
                $templates[] = $basename;
            }
        }

        return array_unique($templates);
    }

    public function clearCache(): void
    {
        $this->cache->clear();
        $this->entityExtractor->clearCache();
    }

    public function registerTemplate(string $templateName, string $content, ?string $context = null): void
    {
        $cacheKey = $this->buildCacheKey($templateName, $context);
        $this->cache->set($cacheKey, $content);
    }

    /**
     * Load template content with hierarchical fallback
     */
    private function loadTemplate(string $templateName, ?string $context): string
    {
        $cacheKey = $this->buildCacheKey($templateName, $context);

        // Check cache first
        $cached = $this->cache->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        // Try to load from filesystem with fallback
        $paths = $this->getTemplatePaths($templateName, $context);

        foreach ($paths as $path) {
            if (file_exists($path)) {
                $content = file_get_contents($path);
                if ($content === false) {
                    continue;
                }

                $this->cache->set($cacheKey, $content);
                return $content;
            }
        }

        // Handle missing template based on strict mode
        if ($this->configuration->strictMode) {
            throw new TemplateNotFoundException($templateName, $context);
        }

        // Return empty content in non-strict mode
        $this->cache->set($cacheKey, '');
        return '';
    }

    /**
     * Get template file paths with hierarchical fallback
     *
     * @return string[]
     */
    private function getTemplatePaths(string $templateName, ?string $context): array
    {
        $paths = [];
        $extension = $this->configuration->templateExtension;
        $directory = $this->configuration->templateDirectory;

        // Try context-specific template first: {context}_template.txt
        if ($context !== null) {
            $paths[] = "{$directory}/{$context}_{$templateName}{$extension}";
        }

        // Fall back to default template: template.txt
        $paths[] = "{$directory}/{$templateName}{$extension}";

        return $paths;
    }

    /**
     * Process template with advanced syntax support
     */
    private function processTemplate(string $template, array $data): string
    {
        if (empty($template)) {
            return '';
        }

        try {
            // Replace placeholders like {{variable}} with data values
            $processed = preg_replace_callback(
                '/\{\{([^}]+)\}\}/',
                fn($matches) => $this->getNestedValue($data, trim($matches[1])) ?? $matches[0],
                $template
            );

            if ($processed === null) {
                throw new InvalidTemplateException($template, 'Variable substitution failed');
            }

            // Process conditional blocks like {{#if condition}}...{{/if}}
            $processed = $this->processConditionals($processed, $data);

            // Process loops like {{#each items}}...{{/each}}
            $processed = $this->processLoops($processed, $data);

            return trim($processed);
        } catch (\Throwable $e) {
            throw new InvalidTemplateException($template, "Processing failed: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Get nested value from data array using dot notation
     */
    private function getNestedValue(array $data, string $key): mixed
    {
        $keys = explode('.', $key);
        $value = $data;

        foreach ($keys as $k) {
            if (is_array($value) && isset($value[$k])) {
                $value = $value[$k];
            } else {
                return null;
            }
        }

        return $value;
    }

    /**
     * Process conditional blocks
     */
    private function processConditionals(string $template, array $data): string
    {
        return preg_replace_callback(
            '/\{\{#if\s+([^}]+)\}\}(.*?)\{\{\/if\}\}/s',
            function ($matches) use ($data) {
                $condition = trim($matches[1]);
                $content = $matches[2];

                $value = $this->getNestedValue($data, $condition);

                // Evaluate condition
                $isTrue = $this->evaluateCondition($value);

                return $isTrue ? $content : '';
            },
            $template
        ) ?? $template;
    }

    /**
     * Process loop blocks
     */
    private function processLoops(string $template, array $data): string
    {
        return preg_replace_callback(
            '/\{\{#each\s+([^}]+)\}\}(.*?)\{\{\/each\}\}/s',
            function ($matches) use ($data) {
                $arrayKey = trim($matches[1]);
                $itemTemplate = $matches[2];

                $items = $this->getNestedValue($data, $arrayKey);
                if (!is_array($items)) {
                    return '';
                }

                $result = '';
                foreach ($items as $index => $item) {
                    // Create context for each item
                    $itemData = is_array($item) ? $item : ['value' => $item, 'index' => $index];
                    $result .= $this->processTemplate($itemTemplate, array_merge($data, $itemData));
                }

                return $result;
            },
            $template
        ) ?? $template;
    }

    /**
     * Evaluate condition for truthiness
     */
    private function evaluateCondition(mixed $value): bool
    {
        if ($value === null || $value === '' || $value === '0') {
            return false;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return $value != 0;
        }

        if (is_array($value)) {
            return count($value) > 0;
        }

        return (string) $value !== '';
    }

    /**
     * Build cache key for template
     */
    private function buildCacheKey(string $templateName, ?string $context): string
    {
        return $context !== null ? "{$context}_{$templateName}" : $templateName;
    }

    /**
     * Get configuration
     */
    public function getConfiguration(): TemplateConfiguration
    {
        return $this->configuration;
    }

    /**
     * Get cache statistics
     *
     * @return array{template_cache: array, entity_cache: array}
     */
    public function getCacheStats(): array
    {
        return [
            'template_cache' => $this->cache->getStats(),
            'entity_cache' => $this->entityExtractor->getCacheStats()
        ];
    }

    /**
     * Get language detector
     */
    public function getLanguageDetector(): LanguageDetector
    {
        return $this->languageDetector;
    }

    /**
     * Get entity extractor
     */
    public function getEntityExtractor(): EntityDataExtractor
    {
        return $this->entityExtractor;
    }
}
