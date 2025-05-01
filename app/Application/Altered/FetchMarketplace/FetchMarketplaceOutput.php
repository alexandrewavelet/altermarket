<?php

namespace App\Application\Altered\FetchMarketplace;

class FetchMarketplaceOutput
{
    public ?string $error = null;

    public array $cardDescriptionFetchingFailures = [];

    public function anErrorOccurredFetchingAlteredMarketplace(string $error): void
    {
        $this->error = $error;
    }

    public function aCardDescriptionCouldNotBeRetrieved(string $identifier): void
    {
        $this->cardDescriptionFetchingFailures[] = $identifier;
    }

    public function couldNotFetchAlteredMarketplace(): bool
    {
        return $this->error !== null;
    }

    public function someCardsCouldNotBeRetrieved(): bool
    {
        return count($this->cardDescriptionFetchingFailures) > 0;
    }

    public function howManyCardsCouldNotBeRetrieved(): int
    {
        return count($this->cardDescriptionFetchingFailures);
    }
}
