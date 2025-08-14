<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Four\TemplateResolver\TemplateResolverFactory;
use Four\TemplateResolver\Configuration\LanguageMapping;
use DateTime;

// Example: Complete marketplace integration scenario
echo "=== Marketplace Integration Example ===\n";

// Entities representing marketplace data structures
class MusicItem
{
    public function __construct(
        private string $sku,
        private string $title,
        private string $artist,
        private string $label,
        private float $price,
        private DateTime $releaseDate,
        private string $format,
        private string $genre,
        private ?string $description = null,
        private array $tracks = []
    ) {
    }

    public function getSku(): string { return $this->sku; }
    public function getTitle(): string { return $this->title; }
    public function getArtist(): string { return $this->artist; }
    public function getLabel(): string { return $this->label; }
    public function getPrice(): float { return $this->price; }
    public function getReleaseDate(): DateTime { return $this->releaseDate; }
    public function getFormat(): string { return $this->format; }
    public function getGenre(): string { return $this->genre; }
    public function getDescription(): ?string { return $this->description; }
    public function getTracks(): array { return $this->tracks; }

    public function getName(): string { return $this->title; } // Alias for compatibility
    public function getFormattedPrice(): string { return number_format($this->price, 2) . ' EUR'; }
}

class MarketplaceOrder
{
    public function __construct(
        private string $marketplaceOrderId,
        private string $marketplace,
        private DateTime $orderDate,
        private string $buyerName
    ) {
    }

    public function getMarketplaceOrderId(): string { return $this->marketplaceOrderId; }
    public function getMarketplace(): string { return $this->marketplace; }
    public function getOrderDate(): DateTime { return $this->orderDate; }
    public function getBuyerName(): string { return $this->buyerName; }
}

class MarketplaceCustomer
{
    public function __construct(
        private string $firstname,
        private string $lastname,
        private string $email,
        private string $country,
        private string $address
    ) {
    }

    public function getFirstname(): string { return $this->firstname; }
    public function getLastname(): string { return $this->lastname; }
    public function getEmail(): string { return $this->email; }
    public function getCountry(): string { return $this->country; }
    public function getAddress(): string { return $this->address; }
    public function getFullName(): string { return $this->firstname . ' ' . $this->lastname; }
}

// Create resolver optimized for European marketplaces
$resolver = TemplateResolverFactory::createEuropean(__DIR__ . '/../templates');

// Example 1: Amazon product listing
echo "=== Amazon Product Listing ===\n";

$metalAlbum = new MusicItem(
    'METAL-001',
    'Paranoid',
    'Black Sabbath',
    'Vertigo Records',
    19.99,
    new DateTime('1970-09-18'),
    'Vinyl LP',
    'Heavy Metal',
    'Second studio album by the pioneers of heavy metal.',
    ['War Pigs', 'Paranoid', 'Planet Caravan', 'Iron Man', 'Electric Funeral', 'Hand of Doom', 'Rat Salad', 'Fairies Wear Boots']
);

if ($resolver->hasTemplate('item_description', 'amazon')) {
    $amazonDescription = $resolver->resolveFromEntity('item_description', $metalAlbum, 'amazon');
    echo $amazonDescription . "\n\n";
}

// Example 2: eBay auction listing
echo "=== eBay Auction Listing ===\n";

if ($resolver->hasTemplate('item_description', 'ebay')) {
    $ebayDescription = $resolver->resolveFromEntity('item_description', $metalAlbum, 'ebay');
    echo $ebayDescription . "\n\n";
}

// Example 3: Multi-marketplace order processing
echo "=== Order Processing for Different Countries ===\n";

$order = new MarketplaceOrder('AMZ-DE-2025-001', 'amazon', new DateTime(), 'Hans Müller');
$germanCustomer = new MarketplaceCustomer('Hans', 'Müller', 'hans@example.de', 'DEU', 'Berlin, Germany');

$usOrder = new MarketplaceOrder('AMZ-US-2025-002', 'amazon', new DateTime(), 'John Smith');
$usCustomer = new MarketplaceCustomer('John', 'Smith', 'john@example.com', 'USA', 'New York, USA');

$frenchOrder = new MarketplaceOrder('AMZ-FR-2025-003', 'amazon', new DateTime(), 'Pierre Dubois');
$frenchCustomer = new MarketplaceCustomer('Pierre', 'Dubois', 'pierre@example.fr', 'FRA', 'Paris, France');

// Process orders with automatic language detection
$orders = [
    ['order' => $order, 'customer' => $germanCustomer, 'label' => 'German'],
    ['order' => $usOrder, 'customer' => $usCustomer, 'label' => 'USA'],
    ['order' => $frenchOrder, 'customer' => $frenchCustomer, 'label' => 'French']
];

foreach ($orders as $orderData) {
    echo "=== {$orderData['label']} Order Comment ===\n";
    
    if ($resolver->hasTemplate('order_comment')) {
        $comment = $resolver->resolveFromEntity('order_comment', 
            [$orderData['order'], $orderData['customer']]
        );
        echo $comment . "\n\n";
    }
}

// Example 4: Performance comparison between marketplaces
echo "=== Performance Comparison ===\n";

$startTime = microtime(true);

// Process 100 Amazon listings
for ($i = 0; $i < 100; $i++) {
    $resolver->resolveFromEntity('item_description', $metalAlbum, 'amazon');
}

$amazonTime = microtime(true) - $startTime;

$startTime = microtime(true);

// Process 100 eBay listings (with caching from previous Amazon calls)
for ($i = 0; $i < 100; $i++) {
    $resolver->resolveFromEntity('item_description', $metalAlbum, 'ebay');
}

$ebayTime = microtime(true) - $startTime;

echo "Amazon processing time: " . number_format($amazonTime * 1000, 2) . " ms\n";
echo "eBay processing time: " . number_format($ebayTime * 1000, 2) . " ms\n";

$stats = $resolver->getCacheStats();
echo "Cache efficiency - Template entries: {$stats['template_cache']['entries']}, ";
echo "Entity types cached: {$stats['entity_cache']['entities']}\n\n";

// Example 5: Custom marketplace configuration
echo "=== Custom Marketplace Configuration ===\n";

// Create resolver with custom language mapping
$customMapping = new LanguageMapping([
    'DEU' => 'german',
    'AUT' => 'german',
    'CHE' => 'german',
    'FRA' => 'french',
    'ITA' => 'italian',
    'ESP' => 'spanish',
    'NLD' => 'dutch',
    'BEL' => 'flemish', // Custom mapping
    'USA' => 'english',
    'GBR' => 'british'  // Custom mapping
], 'english');

$customResolver = TemplateResolverFactory::createWithLanguageMapping(__DIR__ . '/../templates', $customMapping);

$belgianCustomer = new MarketplaceCustomer('Jan', 'Janssens', 'jan@example.be', 'BEL', 'Brussels, Belgium');
$britishCustomer = new MarketplaceCustomer('James', 'Smith', 'james@example.co.uk', 'GBR', 'London, UK');

// Register custom templates
$customResolver->registerTemplate('greeting_flemish', 'Dank je wel {{fullName}} voor je aankoop!');
$customResolver->registerTemplate('greeting_british', 'Cheers {{fullName}}, thanks for your purchase!');

echo "Belgian customer language: " . $customResolver->getLanguageDetector()->detectFromEntity($belgianCustomer) . "\n";
echo "British customer language: " . $customResolver->getLanguageDetector()->detectFromEntity($britishCustomer) . "\n";

$belgianGreeting = $customResolver->resolveFromEntity('greeting', $belgianCustomer);
$britishGreeting = $customResolver->resolveFromEntity('greeting', $britishCustomer);

echo "Belgian: {$belgianGreeting}\n";
echo "British: {$britishGreeting}\n\n";

echo "=== Marketplace Integration Examples completed! ===\n";