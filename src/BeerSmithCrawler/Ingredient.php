<?php

namespace Crawler\BeerSmithCrawler;

/**
 * Class Ingredient.
 */
class Ingredient
{
    public function __construct(
        public string $amount,
        public string $name,
        public string $type
    ) {}

    public static function make(
        string $amount,
        string $name,
        string $type
    ): self {
        return new self(
            $amount,
            $name,
            $type
        );
    }
}
