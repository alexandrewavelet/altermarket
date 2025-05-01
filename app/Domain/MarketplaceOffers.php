<?php

namespace App\Domain;

/**
 * @property Offer[] $offers
 */
readonly class MarketplaceOffers
{
    public function __construct(
        public string $faction,
        public string $card,
        public array $offers,
    ) {
    }
}
