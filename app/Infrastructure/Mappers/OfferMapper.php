<?php

namespace App\Infrastructure\Mappers;

use App\Domain\Offer;
use App\Infrastructure\Laravel\Models\Offer as OfferModel;
use Carbon\CarbonImmutable;

class OfferMapper
{
    public function mapFromApi(array $offer): Offer
    {
        $identifier = str_replace('/cards/', '', $offer['@id']);

        return new Offer(
            identifier: $identifier,
            priceInCents: $offer['lowerPrice'] ? ((int) ($offer['lowerPrice'] * 100)) : null,
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

    public function mapFromModel(OfferModel $model): Offer
    {
        return new Offer(
            identifier: $model->identifier,
            priceInCents: $model->price,
            inSale: $model->in_sale,
            putInSaleAt: CarbonImmutable::parse($model->put_in_sale_at),
            soldAt: $model->sold_at ? CarbonImmutable::parse($model->sold_at) : null,
        );
    }
}
