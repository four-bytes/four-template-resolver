<?php

declare(strict_types=1);

namespace Four\TemplateResolver\Tests\Unit;

use Four\TemplateResolver\Configuration\LanguageMapping;
use Four\TemplateResolver\Configuration\TemplateConfiguration;
use Four\TemplateResolver\Exception\TemplateNotFoundException;
use Four\TemplateResolver\TemplateResolver;
use Four\TemplateResolver\Tests\Fixtures\CustomerEntity;
use Four\TemplateResolver\Tests\Fixtures\MarketplaceOrderEntity;
use Four\TemplateResolver\Tests\Fixtures\SampleEntity;
use Four\TemplateResolver\Tests\TestCase;

class TemplateResolverTest extends TestCase
{
    private TemplateResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $configuration = TemplateConfiguration::withDirectory($this->getTestTemplateDirectory());
        $this->resolver = new TemplateResolver($configuration);
    }

    public function testResolveWithSimpleTemplate(): void
    {
        $this->createTestTemplate('greeting', 'Hello {{name}}!');

        $result = $this->resolver->resolve('greeting', ['name' => 'World']);

        $this->assertEquals('Hello World!', $result);
    }

    public function testResolveFromEntity(): void
    {
        $this->createTestTemplate('item_description', 'Product: {{name}} - Price: €{{price}}');

        $entity = new SampleEntity('Test Product', 29.99);
        $result = $this->resolver->resolveFromEntity('item_description', $entity);

        $this->assertEquals('Product: Test Product - Price: €29.99', $result);
    }

    public function testResolveFromMultipleEntities(): void
    {
        $this->createTestTemplate('order_info', 'Order {{marketplaceId}} for {{fullName}} ({{country}})');

        $order = new MarketplaceOrderEntity('ORDER-123');
        $customer = new CustomerEntity('John', 'Doe', 'USA');

        $result = $this->resolver->resolveFromEntity('order_info', [$order, $customer]);

        $this->assertEquals('Order ORDER-123 for John Doe (USA)', $result);
    }

    public function testHierarchicalFallback(): void
    {
        // Create context-specific and default templates
        $this->createTestTemplate('amazon_item_description', 'Amazon: {{name}} - {{price}}');
        $this->createTestTemplate('item_description', 'Default: {{name}} - {{price}}');

        $data = ['name' => 'Product', 'price' => '19.99'];

        // Should use context-specific template
        $amazonResult = $this->resolver->resolve('item_description', $data, 'amazon');
        $this->assertEquals('Amazon: Product - 19.99', $amazonResult);

        // Should use default template
        $ebayResult = $this->resolver->resolve('item_description', $data, 'ebay');
        $this->assertEquals('Default: Product - 19.99', $ebayResult);
    }

    public function testLanguageDetectionFromEntity(): void
    {
        $this->createTestTemplate('order_comment_german', 'Danke für Ihre Bestellung!');
        $this->createTestTemplate('order_comment_english', 'Thank you for your order!');

        $germanCustomer = new CustomerEntity('Hans', 'Müller', 'DEU');
        $usaCustomer = new CustomerEntity('John', 'Doe', 'USA');

        $germanResult = $this->resolver->resolveFromEntity('order_comment', $germanCustomer);
        $englishResult = $this->resolver->resolveFromEntity('order_comment', $usaCustomer);

        $this->assertEquals('Danke für Ihre Bestellung!', $germanResult);
        $this->assertEquals('Thank you for your order!', $englishResult);
    }

    public function testLanguageOverride(): void
    {
        $this->createTestTemplate('message_german', 'Guten Tag {{firstname}}!');
        $this->createTestTemplate('message_english', 'Good day {{firstname}}!');

        $customer = new CustomerEntity('John', 'Doe', 'USA'); // Would normally be English

        // Override to German
        $result = $this->resolver->resolveFromEntity('message', $customer, null, 'german');
        $this->assertEquals('Guten Tag John!', $result);
    }

    public function testConditionalProcessing(): void
    {
        $template = 'Hello{{#if name}} {{name}}{{/if}}!{{#if premium}} You are premium!{{/if}}';
        $this->createTestTemplate('conditional', $template);

        // With name and premium
        $result1 = $this->resolver->resolve('conditional', ['name' => 'John', 'premium' => true]);
        $this->assertEquals('Hello John! You are premium!', $result1);

        // With name but not premium
        $result2 = $this->resolver->resolve('conditional', ['name' => 'John', 'premium' => false]);
        $this->assertEquals('Hello John!', $result2);

        // Without name
        $result3 = $this->resolver->resolve('conditional', ['premium' => true]);
        $this->assertEquals('Hello! You are premium!', $result3);
    }

    public function testLoopProcessing(): void
    {
        $template = 'Tags: {{#each tags}}{{value}}, {{/each}}';
        $this->createTestTemplate('with_loop', $template);

        $data = ['tags' => ['rock', 'metal', 'progressive']];
        $result = $this->resolver->resolve('with_loop', $data);

        $this->assertStringContainsString('rock', $result);
        $this->assertStringContainsString('metal', $result);
        $this->assertStringContainsString('progressive', $result);
    }

    public function testNestedDataAccess(): void
    {
        $this->createTestTemplate('nested', 'Customer: {{customer.name}} ({{customer.country}})');

        $data = [
            'customer' => [
                'name' => 'John Doe',
                'country' => 'USA'
            ]
        ];

        $result = $this->resolver->resolve('nested', $data);
        $this->assertEquals('Customer: John Doe (USA)', $result);
    }

    public function testHasTemplate(): void
    {
        $this->createTestTemplate('existing', 'Content');

        $this->assertTrue($this->resolver->hasTemplate('existing'));
        $this->assertFalse($this->resolver->hasTemplate('nonexistent'));
    }

    public function testHasTemplateWithContext(): void
    {
        $this->createTestTemplate('amazon_description', 'Amazon content');
        $this->createTestTemplate('description', 'Default content');

        $this->assertTrue($this->resolver->hasTemplate('description', 'amazon'));
        $this->assertTrue($this->resolver->hasTemplate('description', 'ebay')); // Falls back to default
        $this->assertFalse($this->resolver->hasTemplate('nonexistent', 'amazon'));
    }

    public function testGetAvailableTemplates(): void
    {
        $this->createTestTemplate('template1', 'Content 1');
        $this->createTestTemplate('amazon_template2', 'Amazon Content 2');
        $this->createTestTemplate('ebay_template2', 'eBay Content 2');

        $allTemplates = $this->resolver->getAvailableTemplates();
        $this->assertContains('template1', $allTemplates);
        $this->assertContains('amazon_template2', $allTemplates);
        $this->assertContains('ebay_template2', $allTemplates);

        $amazonTemplates = $this->resolver->getAvailableTemplates('amazon');
        $this->assertContains('template2', $amazonTemplates);
        $this->assertNotContains('template1', $amazonTemplates);
    }

    public function testRegisterTemplate(): void
    {
        $this->resolver->registerTemplate('dynamic', 'Hello {{name}}!');

        $result = $this->resolver->resolve('dynamic', ['name' => 'World']);
        $this->assertEquals('Hello World!', $result);
    }

    public function testRegisterTemplateWithContext(): void
    {
        $this->resolver->registerTemplate('greeting', 'Amazon greeting: {{name}}', 'amazon');

        $result = $this->resolver->resolve('greeting', ['name' => 'Customer'], 'amazon');
        $this->assertEquals('Amazon greeting: Customer', $result);
    }

    public function testClearCache(): void
    {
        $this->createTestTemplate('cached', 'Original content');

        // Load template to cache it
        $result1 = $this->resolver->resolve('cached', []);
        $this->assertEquals('Original content', $result1);

        // Update file on disk
        $this->createTestTemplate('cached', 'Updated content');

        // Should still return cached version
        $result2 = $this->resolver->resolve('cached', []);
        $this->assertEquals('Original content', $result2);

        // Clear cache and try again
        $this->resolver->clearCache();
        $result3 = $this->resolver->resolve('cached', []);
        $this->assertEquals('Updated content', $result3);
    }

    public function testStrictModeThrowsException(): void
    {
        $config = TemplateConfiguration::withStrictMode($this->getTestTemplateDirectory());
        $strictResolver = new TemplateResolver($config);

        $this->expectException(TemplateNotFoundException::class);
        $strictResolver->resolve('nonexistent', []);
    }

    public function testNonStrictModeReturnsEmpty(): void
    {
        $result = $this->resolver->resolve('nonexistent', []);
        $this->assertEquals('', $result);
    }

    public function testGetCacheStats(): void
    {
        $this->createTestTemplate('test', 'Hello {{name}}');
        $entity = new SampleEntity('Product', 19.99);

        // Use both template and entity caching
        $this->resolver->resolve('test', ['name' => 'World']);
        $this->resolver->resolveFromEntity('test', $entity);

        $stats = $this->resolver->getCacheStats();

        $this->assertArrayHasKey('template_cache', $stats);
        $this->assertArrayHasKey('entity_cache', $stats);

        $this->assertGreaterThan(0, $stats['template_cache']['entries']);
        $this->assertGreaterThan(0, $stats['entity_cache']['entities']);
    }

    public function testGetConfiguration(): void
    {
        $config = $this->resolver->getConfiguration();
        $this->assertInstanceOf(TemplateConfiguration::class, $config);
        $this->assertEquals($this->getTestTemplateDirectory(), $config->templateDirectory);
    }

    public function testGetLanguageDetector(): void
    {
        $detector = $this->resolver->getLanguageDetector();
        $this->assertInstanceOf(\Four\TemplateResolver\LanguageDetector::class, $detector);
    }

    public function testGetEntityExtractor(): void
    {
        $extractor = $this->resolver->getEntityExtractor();
        $this->assertInstanceOf(\Four\TemplateResolver\EntityDataExtractor::class, $extractor);
    }
}
