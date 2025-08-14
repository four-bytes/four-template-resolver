<?php

declare(strict_types=1);

namespace Four\TemplateResolver\Tests\Unit;

use Four\TemplateResolver\EntityDataExtractor;
use Four\TemplateResolver\Exception\EntityExtractionException;
use Four\TemplateResolver\Tests\Fixtures\CustomerEntity;
use Four\TemplateResolver\Tests\Fixtures\SampleEntity;
use Four\TemplateResolver\Tests\TestCase;
use DateTime;

class EntityDataExtractorTest extends TestCase
{
    private EntityDataExtractor $extractor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->extractor = new EntityDataExtractor();
    }

    public function testExtractFromSimpleEntity(): void
    {
        $entity = new SampleEntity('Test Product', 29.99);
        $data = $this->extractor->extract($entity);

        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('price', $data);
        $this->assertArrayHasKey('available', $data);
        $this->assertArrayHasKey('description', $data);

        $this->assertEquals('Test Product', $data['name']);
        $this->assertEquals(29.99, $data['price']);
        $this->assertEquals('1', $data['available']); // Boolean converted to string
        $this->assertEquals('A test item for unit testing', $data['description']);
    }

    public function testExtractHandlesDateTimeConversion(): void
    {
        $date = new DateTime('2025-06-15');
        $entity = new SampleEntity('Test', 10.0, $date);

        $data = $this->extractor->extract($entity);

        $this->assertArrayHasKey('releaseDate', $data);
        $this->assertEquals('2025-06-15', $data['releaseDate']);
    }

    public function testExtractHandlesArrayConversion(): void
    {
        $entity = new SampleEntity('Test', 10.0, null, ['tag1', 'tag2', 'tag3']);

        $data = $this->extractor->extract($entity);

        $this->assertArrayHasKey('tags', $data);
        $this->assertEquals('tag1, tag2, tag3', $data['tags']);
    }

    public function testExtractIgnoresProblematicProperties(): void
    {
        $entity = new SampleEntity();

        // Should not throw exception even though getProblematicProperty() throws
        $data = $this->extractor->extract($entity);

        // Should contain other properties but not the problematic one
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayNotHasKey('problematicProperty', $data);
    }

    public function testExtractIgnoresNonGetterMethods(): void
    {
        $entity = new SampleEntity();
        $data = $this->extractor->extract($entity);

        // doSomething() is not a getter, should not be included
        $this->assertArrayNotHasKey('something', $data);
    }

    public function testExtractMultiple(): void
    {
        $entity1 = new SampleEntity('Product 1', 19.99);
        $entity2 = new CustomerEntity('John', 'Doe', 'DEU');

        $data = $this->extractor->extractMultiple([$entity1, $entity2]);

        // Should contain data from both entities
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('price', $data);
        $this->assertArrayHasKey('firstname', $data);
        $this->assertArrayHasKey('country', $data);

        $this->assertEquals('Product 1', $data['name']);
        $this->assertEquals('John', $data['firstname']);
        $this->assertEquals('DEU', $data['country']);
    }

    public function testExtractMultipleThrowsExceptionOnNonObject(): void
    {
        $this->expectException(EntityExtractionException::class);
        $this->expectExceptionMessage('Item at index 1 is not an object');

        $entity1 = new SampleEntity();
        /** @phpstan-ignore-next-line */
        $this->extractor->extractMultiple([$entity1, 'not an object']);
    }

    public function testCachingIsWorking(): void
    {
        $entity1 = new SampleEntity('First', 10.0);
        $entity2 = new SampleEntity('Second', 20.0);

        // First extraction should populate cache
        $data1 = $this->extractor->extract($entity1);
        $stats1 = $this->extractor->getCacheStats();

        // Second extraction of same type should use cache
        $data2 = $this->extractor->extract($entity2);
        $stats2 = $this->extractor->getCacheStats();

        $this->assertEquals(1, $stats1['entities']);
        $this->assertEquals(1, $stats2['entities']); // Same entity type, cache reused

        $this->assertEquals('First', $data1['name']);
        $this->assertEquals('Second', $data2['name']); // Values should be different despite cache
    }

    public function testClearCache(): void
    {
        $entity = new SampleEntity();
        $this->extractor->extract($entity);

        $statsBefore = $this->extractor->getCacheStats();
        $this->assertGreaterThan(0, $statsBefore['entities']);

        $this->extractor->clearCache();

        $statsAfter = $this->extractor->getCacheStats();
        $this->assertEquals(0, $statsAfter['entities']);
    }

    public function testDisableCaching(): void
    {
        $extractor = new EntityDataExtractor(false);

        $entity = new SampleEntity();
        $extractor->extract($entity);

        $stats = $extractor->getCacheStats();
        $this->assertEquals(0, $stats['entities']);
    }

    public function testExtractWithComplexReturnTypes(): void
    {
        $entity = new SampleEntity();
        $data = $this->extractor->extract($entity);

        // formattedPrice returns a string, should be included
        $this->assertArrayHasKey('formattedPrice', $data);
        $this->assertStringContainsString('EUR', $data['formattedPrice']);
    }
}
