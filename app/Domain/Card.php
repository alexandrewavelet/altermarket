<?php

namespace App\Domain;

readonly class Card
{
    public function __construct(
        private string $identifier,
        private string $name,
        private string $faction,
        private string $set,
        private string $image,
        private int $mainCost,
        private int $recallCost,
        private int $mountainPower,
        private int $oceanPower,
        private int $forestPower,
        private ?string $mainEffect,
        private ?string $echoEffect,
    )
    {
    }

    public function identifier(): string
    {
        return $this->identifier;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function faction(): string
    {
        return $this->faction;
    }

    public function set(): string
    {
        return $this->set;
    }

    public function image(): string
    {
        return $this->image;
    }

    public function mainCost(): int
    {
        return $this->mainCost;
    }

    public function recallCost(): int
    {
        return $this->recallCost;
    }

    public function mountainPower(): int
    {
        return $this->mountainPower;
    }

    public function oceanPower(): int
    {
        return $this->oceanPower;
    }

    public function forestPower(): int
    {
        return $this->forestPower;
    }

    public function mainEffect(): ?string
    {
        return $this->mainEffect;
    }

    public function echoEffect(): ?string
    {
        return $this->echoEffect;
    }
}
