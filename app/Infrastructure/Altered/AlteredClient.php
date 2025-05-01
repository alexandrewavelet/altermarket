<?php

namespace App\Infrastructure\Altered;

use App\Domain\Card;
use App\Domain\Offer;
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
     * @return Generator<Offer[]>
     * @throws AlteredApiException
     */
    public function getMarketplaceOffers(): Generator
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
                        'factions[]' => 'YZ',
                        'translations.name' => 'caregiver',
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
