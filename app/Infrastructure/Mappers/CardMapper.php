<?php

namespace App\Infrastructure\Mappers;

use App\Domain\Card;

class CardMapper
{
    public function mapFromApi(array $card): Card
    {
        return new Card(
            identifier: $card['reference'],
            name: $card['name'],
            faction: $card['mainFaction']['reference'],
            set: $card['cardSet']['reference'],
            image: $card['imagePath'],
            mainCost: $card['elements']['MAIN_COST'],
            recallCost: $card['elements']['RECALL_COST'],
            mountainPower: $card['elements']['MOUNTAIN_POWER'],
            oceanPower: $card['elements']['OCEAN_POWER'],
            forestPower: $card['elements']['FOREST_POWER'],
            mainEffect: $card['elements']['MAIN_EFFECT'] ?? null,
            echoEffect: $card['elements']['ECHO_EFFECT'] ?? null,
        );
    }
}
