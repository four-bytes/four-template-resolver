<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Four\TemplateResolver\TemplateResolverFactory;
use DateTime;

// Example entities for demonstration
class Product
{
    public function __construct(
        private string $name,
        private float $price,
        private DateTime $releaseDate,
        private array $tags = [],
        private bool $available = true,
        private ?string $description = null
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getReleaseDate(): DateTime
    {
        return $this->releaseDate;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function isAvailable(): bool
    {
        return $this->available;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getFormattedPrice(): string
    {
        return number_format($this->price, 2) . ' EUR';
    }
}

class Customer
{
    public function __construct(
        private string $firstname,
        private string $lastname,
        private string $country,
        private string $email
    ) {
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getFullName(): string
    {
        return $this->firstname . ' ' . $this->lastname;
    }
}

class Order
{
    public function __construct(
        private string $orderId,
        private string $marketplace,
        private DateTime $orderDate
    ) {
    }

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function getMarketplace(): string
    {
        return $this->marketplace;
    }

    public function getOrderDate(): DateTime
    {
        return $this->orderDate;
    }
}

// Example: Entity-based template resolution
echo "=== Entity-Based Template Resolution ===\n";

$resolver = TemplateResolverFactory::createForMarketplaces(__DIR__ . '/../templates');

// Create entities
$product = new Product(
    'Master of Reality',
    24.99,
    new DateTime('1971-07-21'),
    ['metal', 'doom', 'classic'],
    true,
    'Classic heavy metal album by Black Sabbath featuring iconic riffs and dark themes.'
);

$germanCustomer = new Customer('Hans', 'Müller', 'DEU', 'hans@example.com');
$usaCustomer = new Customer('John', 'Doe', 'USA', 'john@example.com');

// Example: Single entity template resolution
$resolver->registerTemplate('product_summary', 
    'Product: {{name}} ({{formattedPrice}}){{#if available}} - Available Now!{{/if}}'
);

$productSummary = $resolver->resolveFromEntity('product_summary', $product);
echo "Product Summary: {$productSummary}\n\n";

// Example: Multi-entity template resolution
echo "=== Multi-Entity Template Resolution ===\n";

$order = new Order('ORD-2025-001', 'amazon', new DateTime('2025-01-15'));

$resolver->registerTemplate('order_confirmation',
    'Order {{orderId}} placed on {{marketplace}} for {{fullName}} ({{country}})\nProduct: {{name}} - {{formattedPrice}}'
);

$confirmationGerman = $resolver->resolveFromEntity('order_confirmation', [$order, $germanCustomer, $product]);
echo "German Order:\n{$confirmationGerman}\n\n";

$confirmationUSA = $resolver->resolveFromEntity('order_confirmation', [$order, $usaCustomer, $product]);
echo "USA Order:\n{$confirmationUSA}\n\n";

// Example: Automatic language detection
echo "=== Automatic Language Detection ===\n";

$resolver->registerTemplate('thank_you_german', 'Vielen Dank {{fullName}} für Ihren Einkauf!');
$resolver->registerTemplate('thank_you_english', 'Thank you {{fullName}} for your purchase!');

$germanThanks = $resolver->resolveFromEntity('thank_you', $germanCustomer);
echo "German Customer: {$germanThanks}\n";

$usaThanks = $resolver->resolveFromEntity('thank_you', $usaCustomer);
echo "USA Customer: {$usaThanks}\n\n";

// Example: Language override
echo "=== Language Override ===\n";

$germanThanksOverride = $resolver->resolveFromEntity('thank_you', $usaCustomer, null, 'german');
echo "USA Customer with German Override: {$germanThanksOverride}\n\n";

// Example: Working with file-based templates
echo "=== File-Based Templates ===\n";

// These will use the actual template files in the templates directory
if ($resolver->hasTemplate('order_comment', null)) {
    $germanComment = $resolver->resolveFromEntity('order_comment', $germanCustomer);
    echo "German Order Comment:\n{$germanComment}\n\n";

    $englishComment = $resolver->resolveFromEntity('order_comment', $usaCustomer);
    echo "English Order Comment:\n{$englishComment}\n\n";
}

// Example: Performance monitoring
echo "=== Performance Monitoring ===\n";

// Perform multiple operations to populate cache
for ($i = 0; $i < 10; $i++) {
    $resolver->resolveFromEntity('product_summary', $product);
}

$stats = $resolver->getCacheStats();
echo "Template Cache Entries: {$stats['template_cache']['entries']}\n";
echo "Template Cache Hits: {$stats['template_cache']['total_hits']}\n";
echo "Entity Cache Entities: {$stats['entity_cache']['entities']}\n";

echo "\n=== Entity Examples completed! ===\n";