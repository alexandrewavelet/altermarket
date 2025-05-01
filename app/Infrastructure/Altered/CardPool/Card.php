<?php

namespace App\Infrastructure\Altered\CardPool;

readonly class Card
{
    public function __construct(
        public string $name,
        public string $faction,
    )
    {
    }
}
