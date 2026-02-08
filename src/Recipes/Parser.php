<?php

namespace Crawler\Recipes;

use Dom\HTMLDocument;

/**
 * Class Parser.
 */
class Parser
{
    public function urls(string $html): array
    {
        $dom = HTMLDocument::createFromString($html, LIBXML_NOERROR);

        $notes = $dom->querySelector('.notes');

        if (!$notes) {
            return [];
        }

        $recipes = $notes->querySelectorAll('.onerecipe');

        $recipesUrls = [];

        foreach ($recipes as $recipe) {
            $recipeDom = HTMLDocument::createFromString($recipe->innerHTML, LIBXML_NOERROR);

            $urlElement = $recipeDom->querySelector('a[title="View Recipe"]');

            if (!$urlElement) {
                continue;
            }

            $url = $urlElement->getAttribute('href');

            if (empty($url)) {
                continue;
            }

            $recipesUrls[] = $url;
        }

        return $recipesUrls;
    }

    public function recipe(string $html): Recipe
    {
        return Recipe::fromHtml($html);
    }
}
