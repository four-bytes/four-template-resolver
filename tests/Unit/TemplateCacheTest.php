<?php

declare(strict_types=1);

namespace Four\TemplateResolver\Tests\Unit;

use Four\TemplateResolver\TemplateCache;
use Four\TemplateResolver\Tests\TestCase;

class TemplateCacheTest extends TestCase
{
    private TemplateCache $cache;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cache = new TemplateCache();
    }

    public function testSetAndGet(): void
    {
        $this->cache->set('test', 'Hello {{name}}');

        $result = $this->cache->get('test');
        $this->assertEquals('Hello {{name}}', $result);
    }

    public function testGetReturnsNullForMissingKey(): void
    {
        $result = $this->cache->get('nonexistent');
        $this->assertNull($result);
    }

    public function testHas(): void
    {
        $this->assertFalse($this->cache->has('test'));

        $this->cache->set('test', 'content');
        $this->assertTrue($this->cache->has('test'));
    }

    public function testRemove(): void
    {
        $this->cache->set('test', 'content');
        $this->assertTrue($this->cache->has('test'));

        $this->cache->remove('test');
        $this->assertFalse($this->cache->has('test'));
    }

    public function testClear(): void
    {
        $this->cache->set('test1', 'content1');
        $this->cache->set('test2', 'content2');

        $this->assertTrue($this->cache->has('test1'));
        $this->assertTrue($this->cache->has('test2'));

        $this->cache->clear();

        $this->assertFalse($this->cache->has('test1'));
        $this->assertFalse($this->cache->has('test2'));
    }

    public function testSetEnabledDisablesCaching(): void
    {
        $this->cache->set('test', 'content');
        $this->assertTrue($this->cache->has('test'));

        $this->cache->setEnabled(false);

        $this->assertFalse($this->cache->has('test')); // Cache cleared when disabled
        $this->assertNull($this->cache->get('test'));

        // Setting while disabled should not store
        $this->cache->set('test2', 'content2');
        $this->assertNull($this->cache->get('test2'));
    }

    public function testIsEnabled(): void
    {
        $this->assertTrue($this->cache->isEnabled());

        $this->cache->setEnabled(false);
        $this->assertFalse($this->cache->isEnabled());
    }

    public function testDisabledCacheAtConstruction(): void
    {
        $cache = new TemplateCache(false);

        $this->assertFalse($cache->isEnabled());

        $cache->set('test', 'content');
        $this->assertNull($cache->get('test'));
    }

    public function testHitCountTracking(): void
    {
        $this->cache->set('test', 'content');

        // First access
        $this->cache->get('test');

        // Second access
        $this->cache->get('test');

        // Third access
        $this->cache->get('test');

        $stats = $this->cache->getStats();
        $this->assertEquals(1, $stats['entries']);
        $this->assertEquals(3, $stats['total_hits']);
        $this->assertEquals(3.0, $stats['hit_rate']);
    }

    public function testGetStats(): void
    {
        // Empty cache
        $stats = $this->cache->getStats();
        $this->assertEquals(0, $stats['entries']);
        $this->assertEquals(0, $stats['total_hits']);
        $this->assertEquals(0.0, $stats['hit_rate']);
        $this->assertNull($stats['most_used']);

        // Add templates and access them
        $this->cache->set('template1', 'content1');
        $this->cache->set('template2', 'content2');

        // Access template1 more often
        $this->cache->get('template1');
        $this->cache->get('template1');
        $this->cache->get('template2');

        $stats = $this->cache->getStats();
        $this->assertEquals(2, $stats['entries']);
        $this->assertEquals(3, $stats['total_hits']);
        $this->assertEquals(1.5, $stats['hit_rate']);
        // Most used should be template1 (2 hits vs 1 hit)
    }

    public function testGetKeys(): void
    {
        $this->assertEquals([], $this->cache->getKeys());

        $this->cache->set('template1', 'content1');
        $this->cache->set('template2', 'content2');

        $keys = $this->cache->getKeys();
        $this->assertCount(2, $keys);
        $this->assertContains('template1', $keys);
        $this->assertContains('template2', $keys);
    }

    public function testGetMemoryUsage(): void
    {
        $initialUsage = $this->cache->getMemoryUsage();
        $this->assertEquals(0, $initialUsage);

        $this->cache->set('test', 'Hello World');

        $usage = $this->cache->getMemoryUsage();
        $this->assertGreaterThan(0, $usage);

        // Add more content
        $this->cache->set('large_template', str_repeat('A', 1000));

        $largerUsage = $this->cache->getMemoryUsage();
        $this->assertGreaterThan($usage, $largerUsage);
    }

    public function testCacheOverwrite(): void
    {
        $this->cache->set('test', 'original');
        $this->assertEquals('original', $this->cache->get('test'));

        $this->cache->set('test', 'updated');
        $this->assertEquals('updated', $this->cache->get('test'));
    }
}
