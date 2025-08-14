<?php

declare(strict_types=1);

namespace Four\TemplateResolver;

use Four\TemplateResolver\Exception\EntityExtractionException;
use ReflectionClass;
use ReflectionMethod;
use DateTime;
use DateTimeInterface;
use Throwable;

/**
 * Extracts data from entities using reflection
 *
 * Converts entity objects to arrays suitable for template processing
 * by analyzing getter methods and converting complex types to strings.
 */
class EntityDataExtractor
{
    /** @var array<string, array<string, mixed>> */
    private array $cache = [];

    private bool $enableCaching;

    public function __construct(bool $enableCaching = true)
    {
        $this->enableCaching = $enableCaching;
    }

    /**
     * Extract data from single entity
     *
     * @param object $entity Entity to extract data from
     * @return array<string, mixed> Extracted data
     * @throws EntityExtractionException When extraction fails
     */
    public function extract(object $entity): array
    {
        $className = $entity::class;

        if ($this->enableCaching && isset($this->cache[$className])) {
            return $this->populateValues($this->cache[$className], $entity);
        }

        try {
            $reflection = new ReflectionClass($entity);
            $structure = $this->analyzeEntityStructure($reflection);

            if ($this->enableCaching) {
                $this->cache[$className] = $structure;
            }

            return $this->populateValues($structure, $entity);
        } catch (Throwable $e) {
            throw new EntityExtractionException(
                $className,
                "Reflection analysis failed: {$e->getMessage()}",
                0,
                $e
            );
        }
    }

    /**
     * Extract data from multiple entities and merge
     *
     * @param object[] $entities Entities to extract data from
     * @return array<string, mixed> Merged data
     * @throws EntityExtractionException When extraction fails
     */
    public function extractMultiple(array $entities): array
    {
        $data = [];

        foreach ($entities as $index => $entity) {
            if (!is_object($entity)) {
                throw new EntityExtractionException(
                    'array',
                    "Item at index {$index} is not an object"
                );
            }

            $entityData = $this->extract($entity);
            $data = array_merge($data, $entityData);
        }

        return $data;
    }

    /**
     * Analyze entity structure and create extraction map
     *
     * @param ReflectionClass<object> $reflection
     * @return array<string, array<string, mixed>>
     */
    private function analyzeEntityStructure(ReflectionClass $reflection): array
    {
        $structure = [];

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $methodName = $method->getName();

            // Check if it's a getter method
            if (!$this->isGetterMethod($method)) {
                continue;
            }

            $propertyName = $this->extractPropertyName($methodName);

            $structure[$propertyName] = [
                'method' => $methodName,
                'type' => $this->determineReturnType($method)
            ];
        }

        return $structure;
    }

    /**
     * Check if method is a getter
     */
    private function isGetterMethod(ReflectionMethod $method): bool
    {
        $methodName = $method->getName();

        // Must be public and take no parameters
        if (!$method->isPublic() || $method->getNumberOfParameters() > 0) {
            return false;
        }

        // Must start with 'get', 'is', or 'has'
        return str_starts_with($methodName, 'get') ||
               str_starts_with($methodName, 'is') ||
               str_starts_with($methodName, 'has');
    }

    /**
     * Extract property name from getter method name
     */
    private function extractPropertyName(string $methodName): string
    {
        if (str_starts_with($methodName, 'get')) {
            return lcfirst(substr($methodName, 3));
        } elseif (str_starts_with($methodName, 'is')) {
            return lcfirst(substr($methodName, 2));
        } elseif (str_starts_with($methodName, 'has')) {
            return lcfirst(substr($methodName, 3));
        }

        return $methodName;
    }

    /**
     * Determine return type from method reflection
     */
    private function determineReturnType(ReflectionMethod $method): string
    {
        $returnType = $method->getReturnType();

        if ($returnType === null) {
            return 'mixed';
        }

        return $returnType->__toString();
    }

    /**
     * Populate actual values from entity using structure
     *
     * @param array<string, array<string, mixed>> $structure
     * @param object $entity
     * @return array<string, mixed>
     */
    private function populateValues(array $structure, object $entity): array
    {
        $data = [];

        foreach ($structure as $propertyName => $info) {
            try {
                $value = $entity->{$info['method']}();
                $data[$propertyName] = $this->convertValue($value);
            } catch (Throwable $e) {
                // Skip properties that can't be accessed
                continue;
            }
        }

        return $data;
    }

    /**
     * Convert complex values to template-friendly formats
     */
    private function convertValue(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        // Convert DateTime objects to string
        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        // Convert arrays to comma-separated strings
        if (is_array($value)) {
            $filtered = array_filter($value, fn($item) => $item !== null && $item !== '');
            return implode(', ', $filtered);
        }

        // Convert objects with __toString to string
        if (is_object($value) && method_exists($value, '__toString')) {
            return (string) $value;
        }

        // Skip complex objects
        if (is_object($value)) {
            return null;
        }

        // Convert boolean to string
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return $value;
    }

    /**
     * Clear extraction cache
     */
    public function clearCache(): void
    {
        $this->cache = [];
    }

    /**
     * Get cache statistics
     *
     * @return array{entities: int, total_properties: int}
     */
    public function getCacheStats(): array
    {
        $totalProperties = array_sum(array_map('count', $this->cache));

        return [
            'entities' => count($this->cache),
            'total_properties' => $totalProperties
        ];
    }
}
