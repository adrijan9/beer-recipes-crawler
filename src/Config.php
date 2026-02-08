<?php

namespace Crawler;

/**
 * Class Config.
 */
class Config
{
    /**
     * --------------------------------------------------------------------------
     * App UI Text
     * --------------------------------------------------------------------------.
     */
    public string $appName = 'Beer Smith Crawler';

    /**
     * --------------------------------------------------------------------------
     * Base URL for Recipe Searches
     * --------------------------------------------------------------------------.
     */
    public string $url = 'https://beersmithrecipes.com/searchrecipe';

    /**
     * --------------------------------------------------------------------------
     * Default Recipes Directory
     * --------------------------------------------------------------------------.
     */
    public string $recipesDir = __DIR__.'/../recipes';

    /**
     * --------------------------------------------------------------------------
     * Default Crawl Settings
     * --------------------------------------------------------------------------.
     */
    public string $defaultTerm = 'lager';
    public int $defaultDepth = 1;
    public string $defaultSort = 'Best Match';

    /**
     * --------------------------------------------------------------------------
     * Crawl UI Options
     * --------------------------------------------------------------------------.
     */
    public array $crawlSortOptions = [
        'Best Match',
        'Rating',
        'Name',
        'Most Recent',
        'ABV (Hi-Lo)',
        'ABV (Lo-Hi)',
        'Color (Hi-Lo)',
        'Color (Lo-Hi)',
        'IBUs (Hi-Lo)',
        'IBUs (Lo-Hi)',
    ];

    public array $recipeTypeLabels = [
        'allgrain' => 'All Grain',
        'partial' => 'Partial Mash',
        'extract' => 'Extract',
        'mead' => 'Mead',
        'cider' => 'Cider',
        'wine' => 'Wine',
    ];

    public array $crawlRatingOptions = [
        ['value' => '0', 'label' => 'Unrated'],
        ['value' => '1', 'label' => '1 beer'],
        ['value' => '2', 'label' => '2 beers'],
        ['value' => '3', 'label' => '3 beers'],
        ['value' => '4', 'label' => '4 beers'],
        ['value' => '5', 'label' => '5 beers'],
    ];

    /**
     * --------------------------------------------------------------------------
     * Recipe UI Options
     * --------------------------------------------------------------------------.
     */
    public array $recipeSortOptions = [
        ['value' => 'default', 'label' => 'Default order'],
        ['value' => 'name', 'label' => 'Name'],
        ['value' => 'rating', 'label' => 'Rating'],
        ['value' => 'bitterness', 'label' => 'Bitterness (IBU)'],
        ['value' => 'color', 'label' => 'Color (SRM)'],
        ['value' => 'batchSize', 'label' => 'Batch size'],
        ['value' => 'estOg', 'label' => 'Est OG'],
        ['value' => 'estFg', 'label' => 'Est FG'],
        ['value' => 'style', 'label' => 'Style'],
        ['value' => 'type', 'label' => 'Type'],
        ['value' => 'fermentation', 'label' => 'Fermentation'],
    ];

    public array $filterRatingOptions = [
        ['value' => 'any', 'label' => 'Any'],
        ['value' => '0', 'label' => 'Unrated'],
        ['value' => '1', 'label' => '1 beer'],
        ['value' => '2', 'label' => '2 beers'],
        ['value' => '3', 'label' => '3 beers'],
        ['value' => '4', 'label' => '4 beers'],
        ['value' => '5', 'label' => '5 beers'],
    ];

    public array $filterRatingComparators = [
        ['value' => '=', 'label' => '='],
        ['value' => '>', 'label' => '>'],
        ['value' => '<', 'label' => '<'],
    ];

    public string $filterRatingComparatorDefault = '=';

    public function uiConfig(): array
    {
        $defaultTypes = [];
        foreach (array_keys($this->recipeTypeLabels) as $key) {
            $defaultTypes[$key] = false;
        }

        $typeOptions = [];
        foreach ($this->recipeTypeLabels as $key => $label) {
            $typeOptions[] = ['key' => $key, 'label' => $label];
        }

        $outputDir = basename($this->recipesDir);
        if ('' !== $outputDir && '/' !== substr($outputDir, -1)) {
            $outputDir .= '/';
        }

        return [
            'app' => [
                'name' => $this->appName,
                'outputDir' => $outputDir,
            ],
            'crawl' => [
                'defaults' => [
                    'term' => $this->defaultTerm,
                    'depth' => $this->defaultDepth,
                    'sort' => $this->defaultSort,
                    'rated' => '0',
                    'types' => $defaultTypes,
                ],
                'sortOptions' => $this->crawlSortOptions,
                'ratingOptions' => $this->crawlRatingOptions,
                'typeOptions' => $typeOptions,
            ],
            'filters' => [
                'ratingOptions' => $this->filterRatingOptions,
                'ratingComparators' => $this->filterRatingComparators,
                'ratingComparatorDefault' => $this->filterRatingComparatorDefault,
            ],
            'recipes' => [
                'sortOptions' => $this->recipeSortOptions,
            ],
            'stats' => [
                'recipeTypeLabels' => $this->recipeTypeLabels,
            ],
        ];
    }
}
