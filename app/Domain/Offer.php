<?php

namespace App\Domain;

use Carbon\CarbonImmutable;

readonly class Offer
{
    public function __construct(
        private string $identifier,
        private ?int $priceInCents,
        private bool $inSale,
        private CarbonImmutable $putInSaleAt,
        private ?CarbonImmutable $soldAt,
    ) {
    }

    public function identifier(): string
    {
        return $this->identifier;
    }

    public function price(): ?float
    {
        if ($this->priceInCents === null) {
            return null;
        }

        return $this->priceInCents / 100;
    }

    public function isInSale(): bool
    {
        return $this->inSale;
    }

    public function inSaleAt(): CarbonImmutable
    {
        return $this->putInSaleAt;
    }

    public function soldAt(): ?CarbonImmutable
    {
        return $this->soldAt;
    }
}
