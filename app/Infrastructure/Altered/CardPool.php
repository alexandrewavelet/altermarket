<?php

namespace App\Infrastructure\Altered;

use App\Infrastructure\Altered\CardPool\Card;

readonly class CardPool
{
    public array $cardsPerFaction;

    public function __construct(
    ) {
        $this->cardsPerFaction = include 'CardPool/card_pool.php';
    }

    /**
     * @return string[]
     */
    public function factions(): array
    {
        return array_keys($this->cardsPerFaction);
    }

    /**
     * @return Card[]
     */
    public function getCardsPerFaction(array $factionsFilter): array
    {
        if (empty($factionsFilter)) {
            $factionsFilter = $this->factions();
        }

        $cards = [];

        foreach ($factionsFilter as $faction) {
            if (isset($this->cardsPerFaction[$faction])) {
                foreach ($this->cardsPerFaction[$faction] as $card) {
                    $cards[] = new Card(
                        name: $card,
                        faction: $faction,
                    );
                }
            }
        }

        return $cards;
    }
}
