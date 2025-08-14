<?php

declare(strict_types=1);

namespace Four\TemplateResolver\Tests\Integration;

use Four\TemplateResolver\Tests\Fixtures\CustomerEntity;
use Four\TemplateResolver\Tests\Fixtures\MarketplaceOrderEntity;
use Four\TemplateResolver\Tests\Fixtures\SampleEntity;
use Four\TemplateResolver\Tests\TestCase;
use Four\TemplateResolver\TemplateResolverFactory;

/**
 * Integration tests simulating real marketplace scenarios
 */
class MarketplaceIntegrationTest extends TestCase
{
    public function testAmazonItemDescription(): void
    {
        $resolver = TemplateResolverFactory::createForMarketplaces($this->getTestTemplateDirectory());

        // Create marketplace-specific and fallback templates
        $this->createTestTemplate(
            'amazon_item_description',
            'Product: {{name}} | Released: {{releaseDate}} | Format: {{formattedPrice}} | Description: {{description}}'
        );

        $this->createTestTemplate(
            'item_description',
            'Default: {{name}} - {{price}} EUR'
        );

        // Test entity similar to ScrItem from original implementation
        $item = new SampleEntity('Heavy Metal Album', 19.99, new \DateTime('2025-01-01'));

        // Should use Amazon-specific template
        $result = $resolver->resolveFromEntity('item_description', $item, 'amazon');

        $this->assertStringContainsString('Product:', $result);
        $this->assertStringContainsString('Released: 2025-01-01', $result);
        $this->assertStringContainsString('Heavy Metal Album', $result);
        $this->assertStringNotContainsString('Default:', $result);
    }

    public function testOrderCommentWithLanguageDetection(): void
    {
        $resolver = TemplateResolverFactory::createForMarketplaces($this->getTestTemplateDirectory());

        // Create language-specific templates
        $this->createTestTemplate(
            'amazon_order_comment_german',
            'Danke für Deine {{marketplace}}-Bestellung {{marketplaceId}}.'
        );

        $this->createTestTemplate(
            'amazon_order_comment_english',
            'Thank you for your {{marketplace}} order {{marketplaceId}}.'
        );

        $this->createTestTemplate(
            'order_comment_german',
            'Danke für Ihre Bestellung.'
        );

        $this->createTestTemplate(
            'order_comment_english',
            'Thank you for your order.'
        );

        // Test entities
        $order = new MarketplaceOrderEntity('ORDER-123', 'amazon', 'Hans Müller');
        $germanCustomer = new CustomerEntity('Hans', 'Müller', 'DEU');
        $usaCustomer = new CustomerEntity('John', 'Doe', 'USA');

        // German customer should get Amazon German template
        $germanResult = $resolver->resolveFromEntity('order_comment', [$order, $germanCustomer], 'amazon');
        $this->assertStringContainsString('Danke für Deine amazon-Bestellung ORDER-123', $germanResult);

        // USA customer should get Amazon English template
        $englishResult = $resolver->resolveFromEntity('order_comment', [$order, $usaCustomer], 'amazon');
        $this->assertStringContainsString('Thank you for your amazon order ORDER-123', $englishResult);
    }

    public function testEbayFallbackToDefault(): void
    {
        $resolver = TemplateResolverFactory::createForMarketplaces($this->getTestTemplateDirectory());

        // Only create Amazon template and default template
        $this->createTestTemplate(
            'amazon_item_description',
            'Amazon: {{name}} - {{price}}'
        );

        $this->createTestTemplate(
            'item_description',
            'Default: {{name}} - {{price}}'
        );

        $item = new SampleEntity('Test Product', 29.99);

        // Amazon should use specific template
        $amazonResult = $resolver->resolveFromEntity('item_description', $item, 'amazon');
        $this->assertStringContainsString('Amazon:', $amazonResult);

        // eBay should fall back to default template
        $ebayResult = $resolver->resolveFromEntity('item_description', $item, 'ebay');
        $this->assertStringContainsString('Default:', $ebayResult);
    }

    public function testComplexTemplateWithConditionals(): void
    {
        $resolver = TemplateResolverFactory::createForMarketplaces($this->getTestTemplateDirectory());

        $template = 'Product: {{name}}{{#if description}} - {{description}}{{/if}}{{#if available}} (Available){{/if}}';
        $this->createTestTemplate('product_info', $template);

        $availableItem = new SampleEntity('Available Product', 19.99, null, [], true, 'Great product');
        $unavailableItem = new SampleEntity('Sold Out Product', 29.99, null, [], false, null);

        $availableResult = $resolver->resolveFromEntity('product_info', $availableItem);
        $this->assertEquals('Product: Available Product - Great product (Available)', $availableResult);

        $unavailableResult = $resolver->resolveFromEntity('product_info', $unavailableItem);
        $this->assertEquals('Product: Sold Out Product', $unavailableResult);
    }

    public function testMultiEntityDataMerging(): void
    {
        $resolver = TemplateResolverFactory::createForMarketplaces($this->getTestTemplateDirectory());

        $template = 'Order {{marketplaceId}} for {{fullName}} ({{country}}) - Item: {{name}} - Total: {{formattedPrice}}';
        $this->createTestTemplate('order_summary', $template);

        $order = new MarketplaceOrderEntity('ORD-456', 'amazon');
        $customer = new CustomerEntity('Jane', 'Smith', 'USA');
        $item = new SampleEntity('Music Album', 24.99);

        $result = $resolver->resolveFromEntity('order_summary', [$order, $customer, $item]);

        $this->assertStringContainsString('Order ORD-456', $result);
        $this->assertStringContainsString('Jane Smith', $result);
        $this->assertStringContainsString('USA', $result);
        $this->assertStringContainsString('Music Album', $result);
        $this->assertStringContainsString('24.99 EUR', $result);
    }

    public function testPerformanceWithCaching(): void
    {
        $resolver = TemplateResolverFactory::createForProduction($this->getTestTemplateDirectory());

        $this->createTestTemplate('cached_template', 'Hello {{name}}!');

        $startTime = microtime(true);

        // First resolution should load from file
        $result1 = $resolver->resolve('cached_template', ['name' => 'World']);

        $firstTime = microtime(true) - $startTime;

        $startTime = microtime(true);

        // Second resolution should use cache
        $result2 = $resolver->resolve('cached_template', ['name' => 'Cache']);

        $secondTime = microtime(true) - $startTime;

        $this->assertEquals('Hello World!', $result1);
        $this->assertEquals('Hello Cache!', $result2);

        // Second call should be faster (cached)
        $this->assertLessThan($firstTime, $secondTime);

        // Verify cache statistics
        $stats = $resolver->getCacheStats();
        $this->assertGreaterThan(0, $stats['template_cache']['entries']);
    }

    public function testErrorHandlingInProduction(): void
    {
        $resolver = TemplateResolverFactory::createForProduction($this->getTestTemplateDirectory());

        // Missing template should return empty string in production
        $result = $resolver->resolve('nonexistent_template', ['name' => 'World']);
        $this->assertEquals('', $result);
    }

    public function testStrictModeInDevelopment(): void
    {
        $resolver = TemplateResolverFactory::createForDevelopment($this->getTestTemplateDirectory());

        $this->expectException(\Four\TemplateResolver\Exception\TemplateNotFoundException::class);

        // Missing template should throw exception in development
        $resolver->resolve('nonexistent_template', ['name' => 'World']);
    }
}
