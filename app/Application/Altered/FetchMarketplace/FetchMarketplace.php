<?php

namespace App\Application\Altered\FetchMarketplace;

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
        private MarketplaceRepository $offersRepository,
    ) {
        $this->output = new FetchMarketplaceOutput();
    }

    public function execute(FetchMarketplaceInput $input): FetchMarketplaceOutput
    {
        $input->fetchMarketplace(
            fn() => $this->fetchMarketplaceOffers()
        );

        if ($this->output->couldNotFetchAlteredMarketplace()) {
            return $this->output;
        }

        $missingCardDescriptionsCount = $this->offersRepository->countOffersWithoutCardDescription();

        $input->retrieveMissingCardDescriptions(
            $missingCardDescriptionsCount,
            fn() => $this->fillMissingCardDescriptions(),
        );

        return $this->output;
    }

    private function fetchMarketplaceOffers(): void
    {
        try {
            foreach ($this->alteredApi->getMarketplaceOffers() as $offers) {
                $this->offersRepository->saveOffers($offers);
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
        $offers = $this->offersRepository->getOffersWithoutCardDescription();

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

        $this->offersRepository->saveCardDescription($card);
    }
}
