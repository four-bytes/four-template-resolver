# Template Syntax Guide

This guide covers the complete template syntax supported by Four Template Resolver, including variables, conditionals, loops, and advanced features.

## Table of Contents

- [Basic Variables](#basic-variables)
- [Conditional Blocks](#conditional-blocks)
- [Loop Blocks](#loop-blocks)
- [Nested Data Access](#nested-data-access)
- [Entity Processing](#entity-processing)
- [Language-Specific Templates](#language-specific-templates)
- [Hierarchical Fallback](#hierarchical-fallback)
- [Best Practices](#best-practices)

## Basic Variables

Variables are enclosed in double curly braces `{{variable}}` and replaced with corresponding data values.

### Simple Variables

```
Hello {{name}}!
Your order {{orderId}} has been processed.
Total: {{formattedPrice}}
```

**Data:**
```php
[
    'name' => 'John Doe',
    'orderId' => 'ORD-12345', 
    'formattedPrice' => '29.99 EUR'
]
```

**Output:**
```
Hello John Doe!
Your order ORD-12345 has been processed.
Total: 29.99 EUR
```

### Variable Formatting

Variables maintain their original type and formatting:

```
Price: {{price}}           // Raw number: 19.99
Formatted: {{formatted}}   // Formatted string: €19.99
Date: {{date}}            // ISO date: 2025-01-15
Boolean: {{available}}    // Boolean as string: 1 or 0
```

## Conditional Blocks

Conditional blocks show content based on variable truthiness using `{{#if condition}}...{{/if}}` syntax.

### Basic Conditionals

```
{{#if premium}}
Welcome Premium Member! You get free shipping.
{{/if}}

{{#if description}}
Description: {{description}}
{{/if}}
```

### Truthiness Rules

**Truthy Values:**
- Non-empty strings
- Numbers ≠ 0
- Boolean `true`
- Non-empty arrays

**Falsy Values:**
- `null`
- Empty string `''`
- String `'0'`
- Number `0`
- Boolean `false`
- Empty arrays

### Conditional Examples

```
Product: {{name}}
{{#if onSale}}
🔥 ON SALE! Save {{discount}}%
{{/if}}

{{#if inStock}}
✅ In Stock - Order now!
{{/if}}

{{#if tags}}
Tags: {{#each tags}}{{value}} {{/each}}
{{/if}}
```

## Loop Blocks

Loop blocks iterate over arrays using `{{#each arrayName}}...{{/each}}` syntax.

### Simple Arrays

```
{{#each colors}}
- {{value}}
{{/each}}
```

**Data:**
```php
['colors' => ['red', 'blue', 'green']]
```

**Output:**
```
- red
- blue  
- green
```

### Object Arrays

```
Track Listing:
{{#each tracks}}
{{index}}. {{title}} - {{duration}}
{{/each}}
```

**Data:**
```php
[
    'tracks' => [
        ['index' => 1, 'title' => 'Song One', 'duration' => '3:45'],
        ['index' => 2, 'title' => 'Song Two', 'duration' => '4:12'],
    ]
]
```

**Output:**
```
Track Listing:
1. Song One - 3:45
2. Song Two - 4:12
```

### Loop Variables

Inside loop blocks, you have access to:

- `{{value}}` - The current item value (for simple arrays)
- `{{index}}` - The current array index
- All properties of the current object (for object arrays)

### Advanced Loop Example

```
Order Items:
{{#each items}}
Item {{@index}}: {{name}} - {{quantity}}x {{price}} = {{total}}
{{#if note}}
  Note: {{note}}
{{/if}}
{{/each}}

Total Items: {{itemCount}}
```

## Nested Data Access

Access nested properties using dot notation `{{parent.child}}`.

### Object Nesting

```
Customer: {{customer.firstName}} {{customer.lastName}}
Email: {{customer.email}}
Phone: {{customer.contact.phone}}
Address: {{customer.address.street}}, {{customer.address.city}}
```

**Data:**
```php
[
    'customer' => [
        'firstName' => 'John',
        'lastName' => 'Doe',
        'email' => 'john@example.com',
        'contact' => [
            'phone' => '+1-555-0123'
        ],
        'address' => [
            'street' => '123 Main St',
            'city' => 'New York'
        ]
    ]
]
```

### Nested Conditionals and Loops

```
{{#if customer.orders}}
Recent Orders:
{{#each customer.orders}}
- Order {{id}}: {{total}} ({{status}})
  {{#if items}}
  Items: {{#each items}}{{name}} {{/each}}
  {{/if}}
{{/each}}
{{/if}}
```

## Entity Processing

The resolver automatically extracts data from PHP objects using reflection.

### Getter Method Mapping

PHP getter methods are automatically mapped to template variables:

```php
class Product
{
    public function getName(): string { return 'Cool Product'; }
    public function getPrice(): float { return 29.99; }
    public function isAvailable(): bool { return true; }
    public function hasDiscount(): bool { return false; }
}
```

**Available Variables:**
- `{{name}}` - from `getName()`
- `{{price}}` - from `getPrice()`
- `{{available}}` - from `isAvailable()`
- `{{discount}}` - from `hasDiscount()`

### Type Conversion

The resolver automatically converts complex types:

**DateTime Objects:**
```php
// DateTime objects become ISO date strings
public function getReleaseDate(): DateTime 
{
    return new DateTime('2025-01-15');
}
// Template: {{releaseDate}} → 2025-01-15
```

**Arrays:**
```php
// Arrays become comma-separated strings
public function getTags(): array 
{
    return ['rock', 'metal', 'classic'];
}
// Template: {{tags}} → rock, metal, classic
```

**Objects with __toString:**
```php
class Price 
{
    public function __toString(): string 
    {
        return '$' . number_format($this->amount, 2);
    }
}
// Template: {{priceObject}} → $19.99
```

### Multi-Entity Processing

Extract data from multiple entities:

```php
$resolver->resolveFromEntity('template', [$order, $customer, $product]);
```

Data from all entities is merged, with later entities overriding earlier ones for duplicate keys.

## Language-Specific Templates

Templates can be language-specific using the `{template}_{language}.txt` naming pattern.

### Automatic Language Detection

```php
class Customer 
{
    public function getCountry(): string { return 'DEU'; }
}

// Automatically selects German template
$resolver->resolveFromEntity('order_comment', $customer);
// Uses: order_comment_german.txt
```

### Language Mappings

**Default Mappings:**
- `DEU`, `AUT`, `CHE` → `german`
- All others → `english`

**European Mappings:**
- `DEU`, `AUT`, `CHE` → `german`
- `FRA` → `french`
- `ITA` → `italian`
- `ESP` → `spanish`
- `NLD` → `dutch`
- `POL` → `polish`
- Others → `english`

### Language Template Examples

**order_comment_german.txt:**
```
Vielen Dank für Ihre Bestellung {{orderId}}!

{{#if customerName}}
Liebe/r {{customerName}},
{{/if}}

Ihre Bestellung wird in Kürze bearbeitet.

Mit freundlichen Grüßen
```

**order_comment_english.txt:**
```
Thank you for your order {{orderId}}!

{{#if customerName}}
Dear {{customerName}},
{{/if}}

Your order will be processed shortly.

Best regards
```

## Hierarchical Fallback

Templates follow a hierarchical resolution system:

### Context-Specific Templates

1. `{context}_{template}_{language}.txt`
2. `{context}_{template}.txt`
3. `{template}_{language}.txt`
4. `{template}.txt`

### Fallback Example

Directory structure:
```
templates/
├── amazon_item_description_german.txt
├── amazon_item_description.txt
├── ebay_item_description.txt
├── item_description_german.txt
└── item_description.txt
```

Resolution for German Amazon listing:
```php
$resolver->resolveFromEntity('item_description', $product, 'amazon', 'german');
```

**Fallback Order:**
1. `amazon_item_description_german.txt` ← Used
2. `amazon_item_description.txt`
3. `item_description_german.txt`
4. `item_description.txt`

Resolution for French eBay listing:
```php
$resolver->resolveFromEntity('item_description', $product, 'ebay', 'french');
```

**Fallback Order:**
1. `ebay_item_description_french.txt` (doesn't exist)
2. `ebay_item_description.txt` ← Used
3. `item_description_french.txt` (doesn't exist)
4. `item_description.txt`

## Best Practices

### Template Organization

**✅ Good:**
```
templates/
├── amazon_product_title.txt
├── amazon_product_description.txt  
├── ebay_product_title.txt
├── ebay_product_description.txt
├── order_comment_german.txt
├── order_comment_english.txt
├── product_title.txt               # Default fallback
└── product_description.txt         # Default fallback
```

**❌ Avoid:**
```
templates/
├── template1.txt                   # Unclear naming
├── desc.txt                        # Abbreviations
├── amazon-product.txt              # Use underscores
└── productTemplate.txt             # Inconsistent casing
```

### Variable Naming

**✅ Good:**
```
{{productName}}
{{customerEmail}}
{{orderDate}}
{{formattedPrice}}
```

**❌ Avoid:**
```
{{name}}          # Too generic
{{e}}             # Abbreviations
{{customer_name}} # Use camelCase
{{PRICE}}         # Uppercase
```

### Conditional Logic

**✅ Good:**
```
{{#if description}}
Description: {{description}}
{{/if}}

{{#if premium}}
Premium benefits include free shipping and priority support.
{{/if}}
```

**❌ Avoid:**
```
{{#if description}}Description: {{description}}{{/if}} <!-- Single line -->

{{#if premium}}{{#if active}}{{#if verified}}...{{/if}}{{/if}}{{/if}} <!-- Too nested -->
```

### Loop Structure

**✅ Good:**
```
Features:
{{#each features}}
- {{name}}: {{description}}
{{/each}}
```

**❌ Avoid:**
```
{{#each features}}{{name}}: {{description}} {{/each}} <!-- Single line -->
```

### Performance Tips

1. **Keep templates simple** - Complex logic slows processing
2. **Use meaningful names** - Self-documenting templates
3. **Avoid deep nesting** - Limit conditional/loop depth
4. **Cache effectively** - Let the resolver handle caching
5. **Test thoroughly** - Verify all code paths

### Error Prevention

1. **Provide fallbacks** - Always have default templates
2. **Handle missing data** - Use conditionals for optional fields
3. **Validate entities** - Ensure required getter methods exist
4. **Test edge cases** - Empty arrays, null values, etc.

---

*For more examples, see the `/examples` directory in the repository.*