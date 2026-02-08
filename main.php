<?php

require 'vendor/autoload.php';

use Crawler\Arg;
use Crawler\BeerSmithCrawler\Crawl;
use Crawler\Config;
use Crawler\File;

function printFullHelp(Config $config): void
{
    $lines = [
        'Usage:',
        '  php crawler [options]',
        '',
        'Options:',
        '  --term <search term>    Search term (default: '.$config->defaultTerm.')',
        '  --depth <pages>         Number of result pages to crawl (default: '.$config->defaultDepth.')',
        '  --sort <sort order>     Sort order (default: '.$config->defaultSort.')',
        '  --rated <0-5>           Rating filter (0 = Unrated, 1-5 = beer rating)',
        '  --types <list>          Comma-separated recipe types (allgrain, partial, extract, mead, cider, wine)',
        '  --help                  Show this help message',
        '',
        'Examples:',
        '  php crawler --term lager --depth 2 --sort "Best Match"',
        '  php crawler --term lager --rated 3 --types "allgrain,extract"',
    ];

    echo implode(PHP_EOL, $lines).PHP_EOL;
}

function printOptionHelp(string $option, Config $config): void
{
    $lines = match ($option) {
        '--term' => [
            'Missing value for --term.',
            '',
            'The --term option sets the search term used for the BeerSmith recipe query.',
            '',
            'Usage:',
            '  --term <search term>',
            '',
            'Example:',
            '  php crawler --term '.$config->defaultTerm,
        ],
        '--depth' => [
            'Missing value for --depth.',
            '',
            'The --depth option sets how many result pages to crawl.',
            '',
            'Usage:',
            '  --depth <pages>',
            '',
            'Example:',
            '  php crawler --depth '.$config->defaultDepth,
        ],
        '--sort' => [
            'Missing value for --sort.',
            '',
            'The --sort option sets the sort order for search results.',
            '',
            'Usage:',
            '  --sort <sort order>',
            '',
            'Example:',
            '  php crawler --sort "'.$config->defaultSort.'"',
        ],
        '--rated' => [
            'Missing value for --rated.',
            '',
            'The --rated option filters recipes by rating (0 = Unrated, 1-5 = beer rating).',
            '',
            'Usage:',
            '  --rated <0-5>',
            '',
            'Example:',
            '  php crawler --rated 3',
        ],
        '--types' => [
            'Missing value for --types.',
            '',
            'The --types option filters recipes by type using a comma-separated list.',
            '',
            'Usage:',
            '  --types <list>',
            '',
            'Example:',
            '  php crawler --types "allgrain,extract"',
        ],
        default => [
            'Missing value for '.$option.'.',
        ],
    };

    echo implode(PHP_EOL, $lines).PHP_EOL;
}

// --------------------
// Construct arguments
// --------------------
$arg = new Arg($argc, $argv);
$config = $arg->config;

if ($arg->has('--help')) {
    printFullHelp($config);

    exit(0);
}

$optionsNeedingValues = [
    '--term',
    '--depth',
    '--sort',
    '--rated',
    '--types',
];
foreach ($optionsNeedingValues as $option) {
    if ($arg->missingValueFor($option)) {
        printOptionHelp($option, $config);

        exit(1);
    }
}

// ------------------
// Construct crawler
// ------------------
$crawl = new Crawl();

$rated = $arg->rated();
$recipeTypes = $arg->types();
$filters = $recipeTypes ?? [];
if (null !== $rated) {
    $filters['rated'] = $rated;
}

// Collected recipes
$recipes = $crawl->recipes($arg->depth(), $arg->term(), $arg->sort(), $filters);
$urls = $crawl->client->requestedUrls();

// ---------------
// Construct file
// ---------------
new File()->save($recipes, $arg->term(), $arg->depth(), $arg->sort(), $recipeTypes, $rated, $urls, null);
