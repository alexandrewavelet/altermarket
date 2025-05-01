<?php

namespace App\Infrastructure;

use App\Domain\Card;
use App\Domain\Offer;
use App\Infrastructure\Laravel\Models\Card as CardModel;
use App\Infrastructure\Laravel\Models\Offer as OfferModel;
use App\Infrastructure\Mappers\OfferMapper;
use Generator;

readonly class MarketplaceRepository
{
    public function __construct(
        private OfferMapper $offerMapper,
    )
    {
    }

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

    public function countOffersWithoutCardDescription(): int
    {
        return OfferModel::query()
            ->whereDoesntHave('card')
            ->count();
    }

    /**
     * @return Generator<Offer>
     */
    public function getOffersWithoutCardDescription(): Generator
    {
        $models = OfferModel::query()
            ->whereDoesntHave('card')
            ->lazy();

        foreach ($models as $model) {
            yield $this->offerMapper->mapFromModel($model);
        }
    }

    public function saveCardDescription(Card $card): void
    {
        $model = new CardModel();

        $model->identifier = $card->identifier();
        $model->name = $card->name();
        $model->faction = $card->faction();
        $model->set = $card->set();
        $model->image = $card->image();
        $model->main_cost = $card->mainCost();
        $model->recall_cost = $card->recallCost();
        $model->mountain_power = $card->mountainPower();
        $model->ocean_power = $card->oceanPower();
        $model->forest_power = $card->forestPower();
        $model->main_effect = $card->mainEffect();
        $model->echo_effect = $card->echoEffect();

        $model->save();

        OfferModel::query()
            ->where('identifier', $card->identifier())
            ->update(['card_id' => $model->id]);
    }
}
