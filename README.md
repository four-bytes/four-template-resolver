# Four Template Resolver

[![PHP Version](https://img.shields.io/badge/php-%5E8.4-blue)](https://www.php.net/)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](https://opensource.org/licenses/MIT)
[![Tests](https://img.shields.io/badge/tests-passing-green)](https://github.com/four-bytes/four-template-resolver)

A modern, high-performance PHP template resolver with entity-based processing, hierarchical fallback, and multi-language support. Perfect for e-commerce marketplaces, multi-tenant applications, and any system requiring flexible template management.

## 🚀 Key Features

- **Entity-Based Processing**: Extract data from objects using reflection
- **Hierarchical Template Fallback**: `{context}_template.txt` → `template.txt`
- **Multi-Language Support**: Automatic language detection from entity properties
- **Advanced Template Syntax**: Variables, conditionals, loops, and nested data access
- **Performance Optimized**: In-memory caching for templates and entity structures
- **Modern PHP 8.4+**: Full type safety with strict typing
- **Factory Pattern**: Multiple pre-configured resolver instances
- **Marketplace Ready**: Optimized for e-commerce integrations

## 📦 Installation

```bash
composer require four-bytes/four-template-resolver
```

## 🎯 Quick Start

### Basic Usage

```php
use Four\TemplateResolver\TemplateResolverFactory;

// Create resolver with template directory
$resolver = TemplateResolverFactory::createWithDirectory('/path/to/templates');

// Simple template resolution
$result = $resolver->resolve('greeting', ['name' => 'World']);
// Output: Content from greeting.txt with {{name}} replaced

// Entity-based resolution
$product = new Product('Cool Item', 19.99);
$description = $resolver->resolveFromEntity('item_description', $product);
```

### Marketplace Integration

```php
// Optimized for e-commerce marketplaces
$resolver = TemplateResolverFactory::createForMarketplaces('/templates');

// Hierarchical fallback: amazon_item_description.txt → item_description.txt
$amazonListing = $resolver->resolveFromEntity('item_description', $product, 'amazon');

// Automatic language detection
$customer = new Customer('Hans', 'Müller', 'DEU');
$comment = $resolver->resolveFromEntity('order_comment', $customer);
// Uses order_comment_german.txt automatically
```

## 📚 Core Concepts

### 1. Entity Processing

The resolver extracts data from objects using reflection, analyzing getter methods to create template variables:

```php
class Product
{
    public function getName(): string { return 'Cool Product'; }
    public function getPrice(): float { return 29.99; }
    public function getReleaseDate(): DateTime { return new DateTime(); }
}

// Automatically extracts: name, price, releaseDate
$resolver->resolveFromEntity('template', $product);
```

### 2. Hierarchical Fallback

Templates follow a hierarchical resolution pattern:

```
Templates Directory:
├── amazon_item_description.txt    ← Used for context 'amazon'
├── ebay_item_description.txt      ← Used for context 'ebay'  
└── item_description.txt           ← Default fallback
```

```php
$resolver->resolve('item_description', $data, 'amazon');
// 1. Tries amazon_item_description.txt
// 2. Falls back to item_description.txt
```

### 3. Language Detection

Automatic language detection from entity properties:

```php
class Customer
{
    public function getCountry(): string { return 'DEU'; }
}

// Automatically detects German and uses template_german.txt
$resolver->resolveFromEntity('template', $customer);
```

**Default Mappings:**
- `DEU`, `AUT`, `CHE` → German
- All others → English

### 4. Advanced Template Syntax

#### Variables
```
Hello {{name}}! Your order {{orderId}} is ready.
```

#### Conditionals
```
{{#if premium}}Welcome Premium Member!{{/if}}
{{#if description}}Description: {{description}}{{/if}}
```

#### Loops
```
Tags: {{#each tags}}{{value}}, {{/each}}

Tracks:
{{#each tracks}}
{{index}}. {{title}} ({{duration}})
{{/each}}
```

#### Nested Data
```
Customer: {{customer.name}} ({{customer.address.country}})
```

## 🏭 Factory Patterns

### Pre-configured Resolvers

```php
// Basic resolver
$resolver = TemplateResolverFactory::createWithDirectory('/templates');

// Strict mode (throws exceptions on missing templates)
$resolver = TemplateResolverFactory::createStrict('/templates');

// European marketplace optimized
$resolver = TemplateResolverFactory::createEuropean('/templates');
// Includes: German, French, Italian, Spanish, Dutch, Polish mappings

// Development (no caching, strict errors)
$resolver = TemplateResolverFactory::createForDevelopment('/templates');

// Production (caching enabled, graceful errors)  
$resolver = TemplateResolverFactory::createForProduction('/templates');
```

### Custom Configuration

```php
use Four\TemplateResolver\Configuration\LanguageMapping;
use Four\TemplateResolver\Configuration\TemplateConfiguration;

$languageMapping = new LanguageMapping([
    'FRA' => 'french',
    'DEU' => 'german',
    'ESP' => 'spanish'
], 'english');

$config = new TemplateConfiguration(
    templateDirectory: '/templates',
    enableCaching: true,
    templateExtension: '.txt',
    strictMode: false,
    languageMapping: $languageMapping
);

$resolver = new TemplateResolver($config);
```

## 📖 Template Syntax Guide

### Variable Substitution

Basic variable replacement using `{{variable}}` syntax:

```
Product: {{name}}
Price: {{formattedPrice}}
Released: {{releaseDate}}
```

### Conditional Blocks

Show content based on variable truthiness:

```
{{#if onSale}}
🔥 SPECIAL OFFER: Save {{discount}}%!
{{/if}}

{{#if description}}
Description: {{description}}
{{/if}}
```

**Truthy values:** Non-empty strings, numbers ≠ 0, `true`, non-empty arrays  
**Falsy values:** `null`, `''`, `'0'`, `0`, `false`, empty arrays

### Loop Blocks

Iterate over arrays:

```
Features:
{{#each features}}
- {{value}}
{{/each}}

Track Listing:
{{#each tracks}}
{{index}}. {{title}} - {{duration}}
{{/each}}
```

**Loop variables:**
- `{{value}}` - Item value (for simple arrays)
- `{{index}}` - Current index
- All item properties (for object arrays)

### Nested Data Access

Access nested properties with dot notation:

```
Customer: {{customer.fullName}}
Address: {{customer.address.street}}, {{customer.address.city}}
Order Total: {{order.total.formatted}}
```

## 🎨 Marketplace Examples

### Amazon Product Description

Template: `amazon_item_description.txt`
```
🎵 {{name}} 🎵

Artist: {{artist}}
Format: {{format}}  
Release Date: {{releaseDate}}
Price: {{formattedPrice}}

{{#if description}}
{{description}}
{{/if}}

{{#if tags}}
Tags: {{#each tags}}#{{value}} {{/each}}
{{/if}}

Perfect for music lovers and collectors!
```

### eBay Auction Listing

Template: `ebay_item_description.txt`  
```
🔥 AUCTION: {{name}} 🔥

🎵 Artist: {{artist}}
📅 Release: {{releaseDate}}
💿 Format: {{format}}
💰 Price: {{formattedPrice}}

📖 DESCRIPTION:
{{description}}

⚡ Fast shipping worldwide!
🛡️ 100% authentic merchandise

Don't miss this opportunity!
```

### Multi-language Order Comments

Template: `order_comment_german.txt`
```
Danke für Ihre {{marketplace}}-Bestellung!

{{#if customerName}}
Liebe/r {{customerName}},
{{/if}}

Ihre Bestellung wurde erfolgreich bearbeitet.

Mit freundlichen Grüßen
```

Template: `order_comment_english.txt`
```
Thank you for your {{marketplace}} order!

{{#if customerName}}
Dear {{customerName}},
{{/if}}

Your order has been successfully processed.

Best regards
```

## 📊 Performance & Caching

### Cache Statistics

```php
$stats = $resolver->getCacheStats();

echo "Template Cache Entries: {$stats['template_cache']['entries']}\n";
echo "Template Cache Hits: {$stats['template_cache']['total_hits']}\n";
echo "Entity Types Cached: {$stats['entity_cache']['entities']}\n";
```

### Cache Management

```php
// Clear all caches
$resolver->clearCache();

// Disable caching (for development)
$resolver = TemplateResolverFactory::createWithoutCaching('/templates');

// Check cache status
$isEnabled = $resolver->getConfiguration()->enableCaching;
```

### Performance Tips

1. **Use caching in production** - Significant performance boost
2. **Cache entity structures** - Reflection analysis is expensive
3. **Minimize template complexity** - Simple templates are faster
4. **Batch operations** - Process multiple items with same templates
5. **Pre-register dynamic templates** - Avoid repeated registrations

## 🧪 Testing

Run the test suite:

```bash
# Install dependencies
composer install

# Run tests
composer test

# Run tests with coverage
composer test-coverage

# Run quality checks
composer quality
```

### Test Coverage

- Unit tests for all core components
- Integration tests for marketplace scenarios  
- Performance tests for caching efficiency
- Mock servers for realistic testing

## 🔧 Configuration Options

### Template Configuration

```php
$config = new TemplateConfiguration(
    templateDirectory: '/path/to/templates',  // Required
    enableCaching: true,                      // Enable template caching
    templateExtension: '.txt',                // Template file extension
    strictMode: false,                        // Throw on missing templates
    languageMapping: $languageMapping        // Country->language mapping
);
```

### Language Mapping

```php
// Default German/English mapping
$mapping = LanguageMapping::germanEnglish();

// European marketplace mapping  
$mapping = LanguageMapping::european();

// Custom mapping
$mapping = new LanguageMapping([
    'FRA' => 'french',
    'ITA' => 'italian',
    'ESP' => 'spanish'
], 'english');
```

## 🚨 Error Handling

### Exception Types

```php
use Four\TemplateResolver\Exception\TemplateNotFoundException;
use Four\TemplateResolver\Exception\InvalidTemplateException;
use Four\TemplateResolver\Exception\EntityExtractionException;

try {
    $result = $resolver->resolveFromEntity('template', $entity);
} catch (TemplateNotFoundException $e) {
    // Template file not found
} catch (InvalidTemplateException $e) {
    // Template syntax error
} catch (EntityExtractionException $e) {
    // Entity data extraction failed
}
```

### Strict vs Non-Strict Mode

**Strict Mode** (Development):
- Throws exceptions on missing templates
- Better error reporting
- Fails fast on issues

**Non-Strict Mode** (Production):  
- Returns empty strings for missing templates
- Graceful degradation
- Continues operation despite errors

## 🤝 Contributing

We welcome contributions! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

### Development Setup

```bash
git clone https://github.com/four-bytes/four-template-resolver
cd four-template-resolver
composer install
composer test
```

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🏢 About 4 Bytes

Four Template Resolver is developed by [4 Bytes](https://4bytes.de), specialists in modern PHP solutions and e-commerce integrations.

**Contact:** info@4bytes.de

---

## 📋 Changelog

### v1.0.0 (2025-01-15)

**Added:**
- Entity-based template processing with reflection
- Hierarchical template fallback system
- Multi-language support with automatic detection
- Advanced template syntax (variables, conditionals, loops)
- Performance-optimized caching system
- Factory pattern with pre-configured resolvers
- Comprehensive test suite with 95%+ coverage
- Professional documentation and examples

**Features:**
- PHP 8.4+ compatibility with strict typing
- PSR-12 compliant code
- PHPStan level 8 analysis
- Marketplace-optimized configurations
- European language mappings
- Development/production modes

---

*Made with ❤️ by the team at 4 Bytes*