<?php

declare(strict_types=1);

namespace Four\TemplateResolver\Tests\Fixtures;

use DateTime;

/**
 * Sample marketplace order entity for testing
 */
class MarketplaceOrderEntity
{
    public function __construct(
        private string $marketplaceId = 'ORDER-12345',
        private string $marketplace = 'amazon',
        private string $buyerName = 'Jane Smith',
        ?DateTime $orderDate = null
    ) {
        $this->orderDate = $orderDate ?? new DateTime('2025-01-15');
    }

    public function getMarketplaceId(): string
    {
        return $this->marketplaceId;
    }

    public function getMarketplace(): string
    {
        return $this->marketplace;
    }

    public function getBuyerName(): string
    {
        return $this->buyerName;
    }

    public function getOrderDate(): DateTime
    {
        return $this->orderDate;
    }

    private DateTime $orderDate;
}
