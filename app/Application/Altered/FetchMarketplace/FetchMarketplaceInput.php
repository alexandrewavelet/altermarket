<?php

namespace App\Application\Altered\FetchMarketplace;

use App\Domain\MarketplaceOffers;
use App\Presenter\Cli\Services\Spinner;
use Generator;
use function Laravel\Prompts\note;
use function Laravel\Prompts\progress;

readonly class FetchMarketplaceInput
{
    public function __construct(
        private array $factions,
    ) {
    }

    public function getFactions(): array
    {
        return $this->factions;
    }

    /**
     * @param Generator<MarketplaceOffers> $fetchMarketplace
     */
    public function fetchMarketplace(Generator $fetchMarketplace): void
    {
        $faction = null;
        $card = null;

        $spinner = new Spinner('Fetching marketplace data');
        $spinner->start();

        foreach ($fetchMarketplace as $marketplaceOffers) {
            if (
                $faction !== $marketplaceOffers->faction
                || $card !== $marketplaceOffers->card
            ) {
                $spinner->stop();

                $faction = $marketplaceOffers->faction;
                $card = $marketplaceOffers->card;

                $spinner = new Spinner("Fetching data for $faction - $card");
                $spinner->start();
            }
        }

        $spinner->stop();

        info('Fetching marketplace data done');
    }

    public function retrieveMissingCardDescriptions(
        int $missingCardDescriptionsCount,
        callable $retrieveCardDescriptions,
    ): void {
        if ($missingCardDescriptionsCount === 0) {
            note('No missing card descriptions to retrieve');

            return;
        }

        $progress = progress(
            label: 'Retrieve missing card descriptions',
            steps: $missingCardDescriptionsCount
        );

        $progress->start();

        foreach ($retrieveCardDescriptions() as $cardRetrieved) {
            $progress->advance();
        }

        $progress->finish();

        info('Retrieving missing card descriptions done');
    }
}
