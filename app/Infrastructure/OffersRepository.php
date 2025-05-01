<?php

namespace App\Infrastructure;

use App\Domain\Offer;
use App\Infrastructure\Laravel\Models\Offer as OfferModel;

class OffersRepository
{
    /**
     * @param Offer[] $offers
     */
    public function saveOffers(array $offers): void
    {
        $data = array_map(function ($offer) {
            return [
                'identifier' => $offer->identifier(),
                'price' => $offer->price(),
                'in_sale' => $offer->isInSale(),
                'put_in_sale_at' => $offer->inSaleAt()->toDateTimeString(),
                'sold_at' => $offer->soldAt()?->toDateTimeString(),
            ];
        }, $offers);

        OfferModel::upsert(
            $data,
            ['identifier'],
            ['price', 'in_sale', 'put_in_sale_at', 'sold_at']
        );
    }
}
