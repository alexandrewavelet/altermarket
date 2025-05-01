<?php

namespace App\Application\Altered\FetchMarketplace;

use function Laravel\Prompts\progress;
use function Laravel\Prompts\spin;

readonly class FetchMarketplaceInput
{
    public function __construct(
    ) {
    }

    public function fetchMarketplace(callable $fetchMarketplaceData): void
    {
        spin(
            $fetchMarketplaceData,
            'Fetching marketplace data...'
        );
    }

    public function retrieveMissingCardDescriptions(
        int $missingCardDescriptionsCount,
        callable $retrieveCardDescriptions,
    ): void {
        if ($missingCardDescriptionsCount === 0) {
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
    }
}
