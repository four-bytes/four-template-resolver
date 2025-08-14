<?php

declare(strict_types=1);

namespace Four\TemplateResolver;

/**
 * In-memory cache for template content
 *
 * Provides caching functionality to avoid repeated file system operations
 * for template loading and processing.
 */
class TemplateCache
{
    /** @var array<string, string> */
    private array $templates = [];

    /** @var array<string, int> */
    private array $hitCounts = [];

    private bool $enabled;

    public function __construct(bool $enabled = true)
    {
        $this->enabled = $enabled;
    }

    /**
     * Get cached template content
     */
    public function get(string $key): ?string
    {
        if (!$this->enabled || !isset($this->templates[$key])) {
            return null;
        }

        $this->hitCounts[$key] = ($this->hitCounts[$key] ?? 0) + 1;
        return $this->templates[$key];
    }

    /**
     * Store template content in cache
     */
    public function set(string $key, string $content): void
    {
        if (!$this->enabled) {
            return;
        }

        $this->templates[$key] = $content;
        $this->hitCounts[$key] = $this->hitCounts[$key] ?? 0;
    }

    /**
     * Check if template is cached
     */
    public function has(string $key): bool
    {
        return $this->enabled && isset($this->templates[$key]);
    }

    /**
     * Remove template from cache
     */
    public function remove(string $key): void
    {
        unset($this->templates[$key], $this->hitCounts[$key]);
    }

    /**
     * Clear entire cache
     */
    public function clear(): void
    {
        $this->templates = [];
        $this->hitCounts = [];
    }

    /**
     * Enable or disable caching
     */
    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;

        if (!$enabled) {
            $this->clear();
        }
    }

    /**
     * Check if caching is enabled
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Get cache statistics
     *
     * @return array{entries: int, total_hits: int, hit_rate: float, most_used: string|null}
     */
    public function getStats(): array
    {
        $totalHits = array_sum($this->hitCounts);
        $entries = count($this->templates);

        $hitRate = $entries > 0 ? $totalHits / $entries : 0.0;

        $mostUsed = null;
        if (!empty($this->hitCounts)) {
            $sortedHits = $this->hitCounts;
            arsort($sortedHits);
            $mostUsed = array_key_first(array_slice($sortedHits, 0, 1, true));
        }

        return [
            'entries' => $entries,
            'total_hits' => $totalHits,
            'hit_rate' => round($hitRate, 2),
            'most_used' => $mostUsed
        ];
    }

    /**
     * Get all cached template keys
     *
     * @return string[]
     */
    public function getKeys(): array
    {
        return array_keys($this->templates);
    }

    /**
     * Get memory usage estimate in bytes
     */
    public function getMemoryUsage(): int
    {
        $size = 0;

        foreach ($this->templates as $key => $content) {
            $size += strlen($key) + strlen($content);
        }

        // Add approximate overhead for array structure
        $size += count($this->templates) * 100; // Rough estimate

        return $size;
    }
}
