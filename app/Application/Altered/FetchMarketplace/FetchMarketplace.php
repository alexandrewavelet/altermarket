<?php

namespace App\Application\Altered\FetchMarketplace;

use App\Infrastructure\Altered\AlteredApiException;
use App\Infrastructure\Altered\AlteredClient;
use App\Infrastructure\OffersRepository;

readonly class FetchMarketplace
{
    private FetchMarketplaceOutput $output;

    public function __construct(
        private AlteredClient $alteredApi,
        private OffersRepository $offersRepository,
    ) {
        $this->output = new FetchMarketplaceOutput();
    }

    public function execute(FetchMarketplaceInput $input): FetchMarketplaceOutput
    {
        $input->fetchMarketplace(function () {
            $this->fetchMarketplaceOffers();
        });

        if ($this->output->couldNotFetchAlteredMarketplace()) {
            return $this->output;
        }

        $this->fillCardsInSaleInfos();

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

    private function fillCardsInSaleInfos(): void
    {
    }
}
