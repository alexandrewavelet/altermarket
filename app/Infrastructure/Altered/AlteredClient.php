<?php

namespace App\Infrastructure\Altered;

use App\Domain\Card;
use App\Domain\MarketplaceOffers;
use App\Domain\Offer;
use App\Infrastructure\Altered\CardPool\Card as AlteredCard;
use App\Infrastructure\Mappers\CardMapper;
use App\Infrastructure\Mappers\OfferMapper;
use Generator;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

readonly class AlteredClient
{
    private Client $client;

    public function __construct(
        private OfferMapper $offerMapper,
        private CardMapper $cardMapper,
        private CardPool $cardPool,
    )
    {
        $this->client = new Client([
            'base_uri' => config('services.altered.api_url'),
            'headers' => [
                'Accept' => 'application/json',
                'Accept-Language' => 'en-en',
                'Authorization' => 'Bearer ' . config('services.altered.bearer_token'),
                'X-consumer-contact' => config('services.altered.consumer_contact'),
            ],
        ]);
    }

    /**
     * @param string[] $factions
     * @return Generator<MarketplaceOffers[]>
     * @throws AlteredApiException
     */
    public function getMarketplaceOffers(array $factions = []): Generator
    {
        $cards = $this->cardPool->getCardsPerFaction($factions);

        foreach ($cards as $card) {
            $marketplaceOffers = $this->getMarketplaceOffersForCard(
                $card
            );

            foreach ($marketplaceOffers as $offers) {
                yield new MarketplaceOffers(
                    faction: $card->faction,
                    card: $card->name,
                    offers: $offers,
                );
            }
        }
    }

    /**
     * @return Generator<Offer[]>
     * @throws AlteredApiException
     */
    private function getMarketplaceOffersForCard(AlteredCard $card): Generator
    {
        $page = 1;
        $hasNextPage = true;

        while ($hasNextPage) {
            try {
                $response = $this->client->get('/cards/stats', [
                    'query' => [
                        'page' => $page,
                        'inSale' => 'true',
                        'rarity[]' => 'UNIQUE',
                        'cardType[]' => 'CHARACTER',
                        'factions[]' => $card->faction,
                        'translations.name' => '"'.$card->name.'"',
                        'locale' => 'fr-fr',
                        'itemsPerPage' => 36,
                        'order[price]' => 'ASC',
                    ],
                ]);
            } catch (GuzzleException $e) {
                throw new AlteredApiException(
                    $e->getCode() === 401
                        ? 'Not logged in to Altered: Please update Bearer token'
                        : "An error occurred while fetching the marketplace offers: {$e->getMessage()}",
                    code: $e->getCode(),
                    previous: $e
                );
            }

            $offers = $this->offerMapper->mapManyFromApi(
                json_decode($response->getBody(), true),
            );

            if (!$offers) {
                $hasNextPage = false;
            } else {
                yield $offers;

                $page++;
            }
        }
    }

    /**
     * @throws AlteredApiException
     */
    public function getCard(string $identifier): Card
    {
        try {
            $response = $this->client->get('/cards/'.$identifier, [
                'query' => [
                    'locale' => 'fr-fr',
                ],
            ]);
        } catch (GuzzleException $e) {
            throw new AlteredApiException(
                $e->getCode() === 401
                    ? 'Not logged in to Altered: Please update Bearer token'
                    : "An error occurred while retrieving card $identifier: {$e->getMessage()}",
                code: $e->getCode(),
                previous: $e
            );
        }

        return $this->cardMapper->mapFromApi(
            json_decode($response->getBody(), true),
        );
    }
}
