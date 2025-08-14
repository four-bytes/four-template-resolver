# API Reference

Complete API documentation for Four Template Resolver.

## Table of Contents

- [Core Classes](#core-classes)
- [Interfaces](#interfaces)
- [Configuration](#configuration)
- [Exceptions](#exceptions)
- [Factory Methods](#factory-methods)

---

## Core Classes

### TemplateResolver

Main template resolution class implementing `TemplateResolverInterface`.

```php
class TemplateResolver implements TemplateResolverInterface
```

#### Constructor

```php
public function __construct(TemplateConfiguration $configuration)
```

Creates a new resolver instance with the given configuration.

**Parameters:**
- `$configuration` - Template configuration object

#### Methods

##### resolveFromEntity()

```php
public function resolveFromEntity(
    string $templateName, 
    object|array $entities, 
    ?string $context = null, 
    ?string $language = null
): string
```

Resolve template content from one or more entities.

**Parameters:**
- `$templateName` - Template name (without .txt extension)
- `$entities` - Single entity or array of entities
- `$context` - Optional context for hierarchical fallback (e.g., 'amazon', 'ebay')
- `$language` - Optional language override

**Returns:** Processed template content

**Throws:**
- `TemplateNotFoundException` - When template is not found (strict mode)
- `InvalidTemplateException` - When template syntax is invalid
- `EntityExtractionException` - When entity data extraction fails

**Example:**
```php
$product = new Product('Cool Item', 19.99);
$result = $resolver->resolveFromEntity('item_description', $product, 'amazon');
```

##### resolve()

```php
public function resolve(
    string $templateName, 
    array $data, 
    ?string $context = null
): string
```

Resolve template content from data array.

**Parameters:**
- `$templateName` - Template name (without .txt extension)
- `$data` - Template variables as associative array
- `$context` - Optional context for hierarchical fallback

**Returns:** Processed template content

**Example:**
```php
$result = $resolver->resolve('greeting', ['name' => 'World'], 'amazon');
```

##### hasTemplate()

```php
public function hasTemplate(string $templateName, ?string $context = null): bool
```

Check if a template exists.

**Parameters:**
- `$templateName` - Template name
- `$context` - Optional context

**Returns:** True if template exists

##### getAvailableTemplates()

```php
public function getAvailableTemplates(?string $context = null): array
```

Get all available templates for a context.

**Parameters:**
- `$context` - Optional context filter

**Returns:** Array of template names

##### clearCache()

```php
public function clearCache(): void
```

Clear template and entity caches.

##### registerTemplate()

```php
public function registerTemplate(
    string $templateName, 
    string $content, 
    ?string $context = null
): void
```

Register template content programmatically.

**Parameters:**
- `$templateName` - Template name
- `$content` - Template content
- `$context` - Optional context

##### getConfiguration()

```php
public function getConfiguration(): TemplateConfiguration
```

Get the current configuration.

**Returns:** Configuration object

##### getCacheStats()

```php
public function getCacheStats(): array
```

Get cache statistics.

**Returns:** Array with template and entity cache statistics

**Example:**
```php
$stats = $resolver->getCacheStats();
// [
//     'template_cache' => ['entries' => 5, 'total_hits' => 42, 'hit_rate' => 8.4],
//     'entity_cache' => ['entities' => 3, 'total_properties' => 15]
// ]
```

---

### EntityDataExtractor

Extracts data from entities using reflection.

```php
class EntityDataExtractor
```

#### Constructor

```php
public function __construct(bool $enableCaching = true)
```

**Parameters:**
- `$enableCaching` - Enable structure caching for performance

#### Methods

##### extract()

```php
public function extract(object $entity): array
```

Extract data from single entity.

**Parameters:**
- `$entity` - Entity to extract data from

**Returns:** Extracted data as associative array

**Throws:** `EntityExtractionException` - When extraction fails

##### extractMultiple()

```php
public function extractMultiple(array $entities): array
```

Extract data from multiple entities and merge.

**Parameters:**
- `$entities` - Array of entities

**Returns:** Merged data from all entities

##### clearCache()

```php
public function clearCache(): void
```

Clear extraction cache.

##### getCacheStats()

```php
public function getCacheStats(): array
```

Get cache statistics.

**Returns:** Array with entities and total_properties counts

---

### LanguageDetector

Detects language from entity properties or context.

```php
class LanguageDetector
```

#### Constructor

```php
public function __construct(LanguageMapping $languageMapping = new LanguageMapping())
```

**Parameters:**
- `$languageMapping` - Country to language mapping

#### Methods

##### detectFromEntity()

```php
public function detectFromEntity(object $entity): string
```

Detect language from entity.

**Parameters:**
- `$entity` - Entity to analyze

**Returns:** Detected language

##### detectFromEntities()

```php
public function detectFromEntities(array $entities): string
```

Detect language from multiple entities.

**Parameters:**
- `$entities` - Entities to analyze

**Returns:** Detected language

##### isValidLanguage()

```php
public function isValidLanguage(string $language): bool
```

Validate language code.

##### normalizeLanguage()

```php
public function normalizeLanguage(string $language): string
```

Normalize language code to consistent format.

##### getLanguageMapping()

```php
public function getLanguageMapping(): LanguageMapping
```

Get language mapping instance.

---

### TemplateCache

In-memory cache for template content.

```php
class TemplateCache
```

#### Constructor

```php
public function __construct(bool $enabled = true)
```

**Parameters:**
- `$enabled` - Enable caching

#### Methods

##### get()

```php
public function get(string $key): ?string
```

Get cached template content.

##### set()

```php
public function set(string $key, string $content): void
```

Store template content in cache.

##### has()

```php
public function has(string $key): bool
```

Check if template is cached.

##### remove()

```php
public function remove(string $key): void
```

Remove template from cache.

##### clear()

```php
public function clear(): void
```

Clear entire cache.

##### getStats()

```php
public function getStats(): array
```

Get cache statistics.

##### getMemoryUsage()

```php
public function getMemoryUsage(): int
```

Get estimated memory usage in bytes.

---

## Interfaces

### TemplateResolverInterface

```php
interface TemplateResolverInterface
{
    public function resolveFromEntity(
        string $templateName, 
        object|array $entities, 
        ?string $context = null, 
        ?string $language = null
    ): string;
    
    public function resolve(
        string $templateName, 
        array $data, 
        ?string $context = null
    ): string;
    
    public function hasTemplate(string $templateName, ?string $context = null): bool;
    public function getAvailableTemplates(?string $context = null): array;
    public function clearCache(): void;
    public function registerTemplate(string $templateName, string $content, ?string $context = null): void;
}
```

---

## Configuration

### TemplateConfiguration

Configuration for template resolver.

```php
class TemplateConfiguration
```

#### Constructor

```php
public function __construct(
    public readonly string $templateDirectory,
    public readonly bool $enableCaching = true,
    public readonly string $templateExtension = '.txt',
    public readonly bool $strictMode = false,
    public readonly LanguageMapping $languageMapping = new LanguageMapping(),
)
```

**Properties:**
- `$templateDirectory` - Path to template directory
- `$enableCaching` - Enable template caching
- `$templateExtension` - File extension for templates
- `$strictMode` - Throw exceptions on missing templates
- `$languageMapping` - Country to language mapping

#### Static Factory Methods

##### withDirectory()

```php
public static function withDirectory(string $templateDirectory): self
```

Create configuration with template directory.

##### withStrictMode()

```php
public static function withStrictMode(string $templateDirectory): self
```

Create configuration with strict mode enabled.

##### withoutCaching()

```php
public static function withoutCaching(string $templateDirectory): self
```

Create configuration with caching disabled.

##### withLanguageMapping()

```php
public static function withLanguageMapping(
    string $templateDirectory, 
    LanguageMapping $languageMapping
): self
```

Create configuration with custom language mapping.

---

### LanguageMapping

Maps country codes to languages.

```php
class LanguageMapping
```

#### Constructor

```php
public function __construct(
    array $countryToLanguage = ['DEU' => 'german', 'AUT' => 'german', 'CHE' => 'german'],
    string $defaultLanguage = 'english'
)
```

**Parameters:**
- `$countryToLanguage` - Country code to language mapping
- `$defaultLanguage` - Default language when country is not mapped

#### Methods

##### getLanguageForCountry()

```php
public function getLanguageForCountry(string $countryCode): string
```

Get language for country code.

##### addCountryMapping()

```php
public function addCountryMapping(string $countryCode, string $language): self
```

Add country mapping (returns new instance).

##### withDefaultLanguage()

```php
public function withDefaultLanguage(string $language): self
```

Set default language (returns new instance).

##### getCountryMappings()

```php
public function getCountryMappings(): array
```

Get all country mappings.

##### getDefaultLanguage()

```php
public function getDefaultLanguage(): string
```

Get default language.

#### Static Factory Methods

##### european()

```php
public static function european(): self
```

Create mapping for European countries.

##### germanEnglish()

```php
public static function germanEnglish(): self
```

Create simple German/English mapping.

---

## Exceptions

### TemplateNotFoundException

```php
class TemplateNotFoundException extends RuntimeException
```

Thrown when a requested template cannot be found.

#### Constructor

```php
public function __construct(
    string $templateName, 
    ?string $context = null, 
    int $code = 0, 
    ?\Throwable $previous = null
)
```

---

### InvalidTemplateException

```php
class InvalidTemplateException extends RuntimeException
```

Thrown when template content contains invalid syntax.

#### Constructor

```php
public function __construct(
    string $templateName, 
    string $reason, 
    int $code = 0, 
    ?\Throwable $previous = null
)
```

---

### EntityExtractionException

```php
class EntityExtractionException extends RuntimeException
```

Thrown when entity data extraction fails.

#### Constructor

```php
public function __construct(
    string $entityClass, 
    string $reason, 
    int $code = 0, 
    ?\Throwable $previous = null
)
```

---

## Factory Methods

### TemplateResolverFactory

Factory for creating TemplateResolver instances.

```php
class TemplateResolverFactory
```

#### Static Methods

##### createWithDirectory()

```php
public static function createWithDirectory(string $templateDirectory): TemplateResolverInterface
```

Create resolver with template directory.

##### createWithConfiguration()

```php
public static function createWithConfiguration(TemplateConfiguration $configuration): TemplateResolverInterface
```

Create resolver with configuration.

##### createStrict()

```php
public static function createStrict(string $templateDirectory): TemplateResolverInterface
```

Create resolver with strict mode enabled.

##### createWithoutCaching()

```php
public static function createWithoutCaching(string $templateDirectory): TemplateResolverInterface
```

Create resolver without caching.

##### createEuropean()

```php
public static function createEuropean(string $templateDirectory): TemplateResolverInterface
```

Create resolver with European language mapping.

##### createWithLanguageMapping()

```php
public static function createWithLanguageMapping(
    string $templateDirectory, 
    LanguageMapping $languageMapping
): TemplateResolverInterface
```

Create resolver with custom language mapping.

##### createForMarketplaces()

```php
public static function createForMarketplaces(string $templateDirectory): TemplateResolverInterface
```

Create resolver optimized for marketplace templates.

##### createFull()

```php
public static function createFull(string $templateDirectory, bool $strictMode = false): TemplateResolverInterface
```

Create resolver with all features enabled.

##### createForDevelopment()

```php
public static function createForDevelopment(string $templateDirectory): TemplateResolverInterface
```

Create resolver for development (no caching, strict mode).

##### createForProduction()

```php
public static function createForProduction(string $templateDirectory): TemplateResolverInterface
```

Create resolver for production (caching enabled, lenient errors).

---

*For practical usage examples, see the [examples directory](../examples/) and [Template Syntax Guide](TEMPLATE_SYNTAX.md).*