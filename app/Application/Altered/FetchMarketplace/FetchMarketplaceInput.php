<?php

namespace App\Application\Altered\FetchMarketplace;

use function Laravel\Prompts\spin;

readonly class FetchMarketplaceInput
{
    public function __construct(
    ) {
    }

    public function fetchMarketplace(callable $fetch): void
    {
        spin(
            $fetch,
            'Fetching marketplace data...'
        );
    }
}
