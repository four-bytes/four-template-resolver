<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Four\TemplateResolver\TemplateResolverFactory;

// Example: Basic template resolution with data array
echo "=== Basic Template Resolution ===\n";

$resolver = TemplateResolverFactory::createWithDirectory(__DIR__ . '/../templates');

// Simple template with data array
$resolver->registerTemplate('greeting', 'Hello {{name}}! Welcome to {{service}}.');

$result = $resolver->resolve('greeting', [
    'name' => 'John',
    'service' => 'Four Template Resolver'
]);

echo "Result: {$result}\n\n";

// Example: Hierarchical fallback
echo "=== Hierarchical Template Fallback ===\n";

// This will try 'amazon_item_description.txt' first, then 'item_description.txt'
$itemData = [
    'name' => 'Heavy Metal Album',
    'artist' => 'Iron Maiden',
    'format' => 'Vinyl LP',
    'releaseDate' => '2025-01-15',
    'formattedPrice' => '29.99 EUR',
    'description' => 'A fantastic heavy metal album with powerful vocals and guitar riffs.'
];

$amazonResult = $resolver->resolve('item_description', $itemData, 'amazon');
echo "Amazon Description:\n{$amazonResult}\n\n";

$ebayResult = $resolver->resolve('item_description', $itemData, 'ebay');
echo "eBay Description:\n{$ebayResult}\n\n";

// Example: Conditional templates
echo "=== Conditional Templates ===\n";

$resolver->registerTemplate('product_info', 
    'Product: {{name}}{{#if onSale}} (ON SALE!){{/if}}{{#if description}} - {{description}}{{/if}}'
);

$productOnSale = $resolver->resolve('product_info', [
    'name' => 'Special Edition Album',
    'onSale' => true,
    'description' => 'Limited edition with bonus tracks'
]);

$productNormal = $resolver->resolve('product_info', [
    'name' => 'Regular Album',
    'onSale' => false,
    'description' => null
]);

echo "On Sale: {$productOnSale}\n";
echo "Normal: {$productNormal}\n\n";

// Example: Loop templates
echo "=== Loop Templates ===\n";

$resolver->registerTemplate('track_list', 
    'Album: {{albumName}}\nTracks:\n{{#each tracks}}{{index}}. {{title}} ({{duration}})\n{{/each}}'
);

$albumData = [
    'albumName' => 'Master of Puppets',
    'tracks' => [
        ['index' => 1, 'title' => 'Battery', 'duration' => '5:13'],
        ['index' => 2, 'title' => 'Master of Puppets', 'duration' => '8:35'],
        ['index' => 3, 'title' => 'The Thing That Should Not Be', 'duration' => '6:36']
    ]
];

$trackListResult = $resolver->resolve('track_list', $albumData);
echo $trackListResult . "\n";

echo "=== Examples completed! ===\n";