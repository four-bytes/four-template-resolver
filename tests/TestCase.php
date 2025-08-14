<?php

declare(strict_types=1);

namespace Four\TemplateResolver\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * Base test case for template resolver tests
 */
abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->createTestTemplateDirectory();
    }

    protected function tearDown(): void
    {
        $this->cleanupTestTemplateDirectory();
        parent::tearDown();
    }

    /**
     * Get temporary directory for test templates
     */
    protected function getTestTemplateDirectory(): string
    {
        return sys_get_temp_dir() . '/four-template-resolver-tests';
    }

    /**
     * Create test template directory
     */
    private function createTestTemplateDirectory(): void
    {
        $dir = $this->getTestTemplateDirectory();

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    /**
     * Clean up test template directory
     */
    private function cleanupTestTemplateDirectory(): void
    {
        $dir = $this->getTestTemplateDirectory();

        if (is_dir($dir)) {
            $this->removeDirectory($dir);
        }
    }

    /**
     * Remove directory recursively
     */
    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir) ?: [], ['.', '..']);

        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;

            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }

    /**
     * Create a test template file
     */
    protected function createTestTemplate(string $name, string $content): void
    {
        $path = $this->getTestTemplateDirectory() . '/' . $name . '.txt';
        file_put_contents($path, $content);
    }

    /**
     * Assert that a template file exists
     */
    protected function assertTemplateExists(string $name): void
    {
        $path = $this->getTestTemplateDirectory() . '/' . $name . '.txt';
        $this->assertFileExists($path, "Template '{$name}' should exist");
    }

    /**
     * Assert that a template file does not exist
     */
    protected function assertTemplateDoesNotExist(string $name): void
    {
        $path = $this->getTestTemplateDirectory() . '/' . $name . '.txt';
        $this->assertFileDoesNotExist($path, "Template '{$name}' should not exist");
    }
}
