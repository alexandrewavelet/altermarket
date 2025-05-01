<?php

namespace App\Presenter\Cli;

use App\Application\Altered\FetchMarketplace\FetchMarketplace as FetchMarketplaceAction;
use App\Application\Altered\FetchMarketplace\FetchMarketplaceInput;
use Illuminate\Console\Command;

class FetchMarketplace extends Command
{
    protected $signature = 'altered:fetch-marketplace';

    protected $description = 'Fetch the Altered marketplace data';

    public function __construct(
        private readonly FetchMarketplaceAction $fetchMarketplace,
    )
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $output = $this->fetchMarketplace->execute(new FetchMarketplaceInput());

        if ($output->couldNotFetchAlteredMarketplace()) {
            $this->error($output->error);

            return self::FAILURE;
        }

        $this->info('Marketplace data retrieved!');

        return self::SUCCESS;
    }
}
