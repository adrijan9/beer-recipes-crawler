<?php

namespace Crawler\Recipes;

use Dom\Element;
use Dom\HTMLDocument;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Class Recipe.
 */
class Recipe
{
    public function __construct(
        public string $name,
        public int $rating,
        public string $type,
        public string $glass,
        public string $style,
        public string $batchSize,
        public string $color,
        public string $bitterness,
        public string $estOg,
        public string $estFg,
        public string $fermentation,
        public array $ingredients,
    ) {}

    public static function make(
        string $name,
        int $rating,
        string $type,
        string $glass,
        string $style,
        string $batchSize,
        string $color,
        string $bitterness,
        string $estOg,
        string $estFg,
        string $fermentation,
        array $ingredients,
    ): self {
        return new self(
            $name,
            $rating,
            $type,
            $glass,
            $style,
            $batchSize,
            $color,
            $bitterness,
            $estOg,
            $estFg,
            $fermentation,
            $ingredients,
        );
    }

    public static function fromHtml(string $html): self
    {
        $dom = HTMLDocument::createFromString($html, LIBXML_NOERROR);

        $main = $dom->querySelector('#main');

        if (!$main) {
            throw new \InvalidArgumentException('Invalid HTML: Missing #main element');
        }

        $name = self::getName($main);
        $rating = self::getRating($main);
        $type = self::getType($main);
        $glass = self::getGlass($main);

        [
            'batchSize' => $batchSize,
            'color' => $color,
            'style' => $style,
            'bitterness' => $bitterness,
            'estOg' => $estOg,
            'estFg' => $estFg,
            'fermentation' => $fermentation,
        ] = self::getData($main);

        $ingredients = self::getIngredients($main);

        return self::make(
            name: $name ?? '',
            rating: $rating ?? 0,
            type: $type ?? '',
            glass: $glass ?? '',
            style: $style ?? '',
            batchSize: $batchSize ?? '',
            color: $color ?? '',
            bitterness: $bitterness ?? '',
            estOg: $estOg ?? '',
            estFg: $estFg ?? '',
            fermentation: $fermentation ?? '',
            ingredients: $ingredients,
        );
    }

    private static function getName(Element $main): ?string
    {
        $h2Title = $main->querySelector('h2');

        if (!$h2Title) {
            return null;
        }

        $h2Title = explode('(', $h2Title->textContent);

        if (!isset($h2Title[0])) {
            return null;
        }

        return trim($h2Title[0]);
    }

    private static function getRating(Element $main): ?int
    {
        $h2Title = $main->querySelector('h2');

        if (!$h2Title) {
            return null;
        }

        $img = $h2Title->querySelector('img');

        if (!$img) {
            return null;
        }

        $src = $img->getAttribute('src') ?? '';
        $src = explode('/', $src);
        $filename = end($src);
        $filename = explode('.', $filename)[0];
        $rating = str_replace(['star', '0'], ['', ''], $filename);

        return (int) $rating;
    }

    private static function getType(Element $main): ?string
    {
        $type = $main->querySelector('h3');

        if (!$type) {
            return null;
        }

        return trim($type->textContent);
    }

    private static function getGlass(Element $main): ?string
    {
        $glass = $main->querySelector('.glass');

        if (!$glass) {
            return null;
        }

        $img = $glass->querySelector('img');

        if (!$img) {
            return null;
        }

        return trim($img->getAttribute('src') ?? '');
    }

    #[ArrayShape([
        'batchSize' => 'string',
        'color' => 'string',
        'bitterness' => 'string',
        'estOg' => 'string',
        'estFg' => 'string',
        'fermentation' => 'string',
    ])]
    private static function getData(Element $main): array
    {
        $table = $main->querySelector('.r_hdr');

        if (!$table) {
            return [];
        }

        $rows = $table->querySelectorAll('tr');

        $data = [];

        foreach ($rows as $row) {
            $cols = $row->querySelectorAll('td');

            foreach ($cols as $col) {
                $colType = $col->querySelector('b');

                if (!$colType) {
                    continue;
                }

                $text = $colType->textContent;

                if (str_contains($text, 'Batch Size')) {
                    $value = explode(':', $col->textContent)[1];
                    $value = trim($value);

                    $data['batchSize'] = $value;
                } elseif (str_contains($text, 'Color')) {
                    $value = explode(':', $col->textContent)[1];
                    $value = trim($value);

                    $data['color'] = $value;
                } elseif (str_contains($text, 'Style:')) {
                    $value = explode(':', $col->textContent)[1];
                    $value = trim($value);

                    $data['style'] = $value;
                } elseif (str_contains($text, 'Bitterness')) {
                    $value = explode(':', $col->textContent)[1];
                    $value = trim($value);

                    $data['bitterness'] = $value;
                } elseif (str_contains($text, 'Est OG')) {
                    $value = explode(':', $col->textContent)[1];
                    $value = trim($value);

                    $data['estOg'] = $value;
                } elseif (str_contains($text, 'Est FG')) {
                    $value = explode(':', $col->textContent)[1];
                    $value = trim($value);

                    $data['estFg'] = $value;
                } elseif (str_contains($text, 'Fermentation')) {
                    $value = explode(':', $col->textContent)[1];
                    $value = trim($value);

                    $data['fermentation'] = $value;
                }
            }
        }

        return $data;
    }

    private static function getIngredients(Element $main): array
    {
        $recipesTable = $main->querySelector('table.recipes');

        if (!$recipesTable) {
            return [];
        }

        $rows = $recipesTable->querySelectorAll('tr');

        $ingredients = [];

        foreach ($rows as $row) {
            $cols = $row->querySelectorAll('td');

            if (count($cols) < 2) {
                continue;
            }

            $amount = trim($cols[0]->textContent);
            $name = trim($cols[1]->textContent);
            $type = trim($cols[2]->textContent);

            $ingredients[] = Ingredient::make($amount, $name, $type);
        }

        return $ingredients;
    }
}
