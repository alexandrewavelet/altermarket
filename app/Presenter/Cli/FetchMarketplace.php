<?php

namespace App\Presenter\Cli;

use App\Application\Altered\FetchMarketplace\FetchMarketplace as FetchMarketplaceAction;
use App\Application\Altered\FetchMarketplace\FetchMarketplaceInput;
use Illuminate\Console\Command;
use function Laravel\Prompts\outro;

class FetchMarketplace extends Command
{
    protected $signature = 'altered:fetch-marketplace {--faction=*}';

    protected $description = 'Fetch the Altered marketplace data';

    public function __construct(
        private readonly FetchMarketplaceAction $fetchMarketplace,
    )
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $output = $this->fetchMarketplace->execute(
            new FetchMarketplaceInput($this->option('faction') ?: [])
        );

        if ($output->couldNotFetchAlteredMarketplace()) {
            $this->error($output->error);

            return self::FAILURE;
        }

        if ($output->someCardsCouldNotBeRetrieved()) {
            $this->warn(
                $output->howManyCardsCouldNotBeRetrieved().' cards could not be retrieved'
            );
        }

        outro('Marketplace data retrieved!');

        return self::SUCCESS;
    }
}
