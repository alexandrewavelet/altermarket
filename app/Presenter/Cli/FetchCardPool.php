<?php

namespace App\Presenter\Cli;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Throwable;
use function Laravel\Prompts\note;
use function Laravel\Prompts\spin;

class FetchCardPool extends Command
{
    private const CARD_POOL_PATH = 'App/Infrastructure/Altered/CardPool/card_pool.php';

    protected $signature = 'altered:fetch-card-pool';

    protected $description = 'Fetch the Altered card pool';

    public function handle(): int
    {
        try {
            $cardPool = spin(
                fn () => $this->getCardPoolFromAltered(),
                'Fetching card pool from Altered API...',
            );
        } catch (Throwable $e) {
            $this->error('Error fetching card pool: ' . $e->getMessage());

            return self::FAILURE;
        }

        $this->saveCardPool($cardPool);

        note('Card pool saved to ' . self::CARD_POOL_PATH);

        $this->info('Card pool data retrieved!');

        return self::SUCCESS;
    }

    /**
     * @throws GuzzleException
     */
    private function getCardPoolFromAltered()
    {
        $cards = collect();

        $client = new Client([
            'base_uri' => config('services.altered.api_url'),
            'headers' => [
                'Accept' => '*/*',
                'Accept-Language' => 'en-us',
                'X-consumer-contact' => config('services.altered.consumer_contact'),
                'Cache-control' => 'no-cache',
                'dnt' => '1',
                'pragma' => 'no-cache',
                'origin' => 'https://altered.gg',
                'priority' => 'u=1, i',
                'referer' => 'https://altered.gg/',
                'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36',
            ],
        ]);

        $query = http_build_query([
            'cardSet' => ['ALIZE', 'CORE'],
            'cardType[]' => 'CHARACTER',
            'isExclusive' => 'false',
            'rarity[]' => 'RARE',
            'itemsPerPage' => 36,
            'locale' => 'en-us',
        ]);
        $query = preg_replace('/%5B\d+%5D/', '%5B%5D', $query);

        $response = $client->get('/cards', [
            'query' => $query,
        ]);

        $page = json_decode($response->getBody(), true);
        $nextPage = $page['hydra:view']['hydra:next'] ?? false;

        $cards = $cards->merge($page['hydra:member']);

        while ($nextPage) {
            $response = $client->get($nextPage);

            $page = json_decode($response->getBody(), true);
            $nextPage = $page['hydra:view']['hydra:next'] ?? false;

            $cards = $cards->merge($page['hydra:member']);
        }

        return $cards
            ->sortBy('name')
            ->mapToGroups(function ($card) {
                return [$card['mainFaction']['reference'] => $card['name']];
            })->sortKeys();
    }

    private function saveCardPool(Collection $cardPool): void
    {
        $fileContent = "<?php\n\nreturn [\n";

        foreach ($cardPool as $faction => $cards) {
            $fileContent .= "    '$faction' => [\n";

            foreach ($cards as $card) {
                $escapedCardName = addslashes($card);
                $fileContent .= "        '$escapedCardName',\n";
            }

            $fileContent .= "    ],\n";
        }

        $fileContent .= "];\n";

        file_put_contents(
            base_path(self::CARD_POOL_PATH),
            $fileContent
        );
    }
}
