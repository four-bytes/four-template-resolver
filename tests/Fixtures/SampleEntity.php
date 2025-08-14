<?php

declare(strict_types=1);

namespace Four\TemplateResolver\Tests\Fixtures;

use DateTime;

/**
 * Sample entity for testing entity data extraction
 */
class SampleEntity
{
    public function __construct(
        private string $name = 'Test Item',
        private float $price = 19.99,
        ?DateTime $releaseDate = null,
        private array $tags = ['rock', 'metal'],
        private bool $available = true,
        private ?string $description = 'A test item for unit testing'
    ) {
        $this->releaseDate = $releaseDate ?? new DateTime('2025-01-01');
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

    // Method that throws exception to test error handling
    public function getProblematicProperty(): string
    {
        throw new \RuntimeException('This property cannot be accessed');
    }

    // Non-getter method should be ignored
    public function doSomething(): void
    {
        // This should not be extracted
    }

    // Properties
    private DateTime $releaseDate;
}
