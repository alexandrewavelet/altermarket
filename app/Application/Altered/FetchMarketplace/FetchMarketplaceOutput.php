<?php

namespace App\Application\Altered\FetchMarketplace;

class FetchMarketplaceOutput
{
    public ?string $error = null;

    public function __construct(
    ) {}

    public function anErrorOccurredFetchingAlteredMarketplace(string $error): void
    {
        $this->error = $error;
    }

    public function couldNotFetchAlteredMarketplace(): bool
    {
        return $this->error !== null;
    }
}
