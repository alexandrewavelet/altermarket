<?php

namespace App\Infrastructure\Mappers;

use App\Domain\Offer;
use Carbon\CarbonImmutable;

class OfferMapper
{
    public function mapFromApi(array $offer): Offer
    {
        $identifier = str_replace('/cards/', '', $offer['@id']);

        return new Offer(
            identifier: $identifier,
            price: $offer['price'] ?? null,
            inSale: true,
            putInSaleAt: CarbonImmutable::now(),
            soldAt: null,
        );
    }

    /**
     * @return Offer[]
     */
    public function mapManyFromApi(array $offers): array
    {
        return array_map(
            fn ($offer) => $this->mapFromApi($offer),
            $offers
        );
    }
}
