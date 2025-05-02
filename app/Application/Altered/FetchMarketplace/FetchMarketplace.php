<?php

namespace App\Application\Altered\FetchMarketplace;

use App\Domain\MarketplaceOffers;
use App\Domain\Offer;
use App\Infrastructure\Altered\AlteredApiException;
use App\Infrastructure\Altered\AlteredClient;
use App\Infrastructure\MarketplaceRepository;
use Generator;

readonly class FetchMarketplace
{
    private FetchMarketplaceOutput $output;

    public function __construct(
        private AlteredClient $alteredApi,
        private MarketplaceRepository $marketplaceRepository,
    ) {
        $this->output = new FetchMarketplaceOutput();
    }

    public function execute(FetchMarketplaceInput $input): FetchMarketplaceOutput
    {
        $this->marketplaceRepository->putAllOffersOutOfSale(
            $input->getFactions()
        );

        $input->fetchMarketplace(
            $this->fetchMarketplaceOffers($input),
        );

        if ($this->output->couldNotFetchAlteredMarketplace()) {
            return $this->output;
        }

        $missingCardDescriptionsCount = $this->marketplaceRepository
            ->countOffersWithoutCardDescription();

        $input->retrieveMissingCardDescriptions(
            $missingCardDescriptionsCount,
            fn() => $this->fillMissingCardDescriptions(),
        );

        $this->marketplaceRepository->setSoldAtToNowForSoldOffersWithoutSoldDate(
            $input->getFactions()
        );

        return $this->output;
    }

    /**
     * @return Generator<MarketplaceOffers>
     */
    private function fetchMarketplaceOffers(FetchMarketplaceInput $input): Generator
    {
        try {
            $marketplaceOffers = $this->alteredApi->getMarketplaceOffers(
                $input->getFactions()
            );

            foreach ($marketplaceOffers as $offers) {
                yield $offers;

                $this->marketplaceRepository->saveOffers($offers->offers);
            }
        } catch (AlteredApiException $e) {
            $this->output->anErrorOccurredFetchingAlteredMarketplace(
                $e->getMessage()
            );
        }
    }

    /**
     * @return Generator<null> that yields when a card description is retrieved
     */
    private function fillMissingCardDescriptions(): Generator
    {
        $offers = $this->marketplaceRepository->getOffersWithoutCardDescription();

        foreach ($offers as $offer) {
            $this->fillCardDescription($offer);

            yield;
        }
    }

    private function fillCardDescription(Offer $offer): void
    {
        try {
            $card = $this->alteredApi->getCard($offer->identifier());

        } catch (AlteredApiException) {
            $this->output->aCardDescriptionCouldNotBeRetrieved(
                $offer->identifier(),
            );

            return;
        }

        $this->marketplaceRepository->saveCardDescription($card);
    }
}
