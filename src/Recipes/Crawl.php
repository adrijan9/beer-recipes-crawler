<?php

namespace Crawler\Recipes;

use Crawler\Client;
use Crawler\Config;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class Crawler.
 */
class Crawl
{
    public Client $client;
    public Parser $parser;
    public Config $config;

    public function __construct()
    {
        $this->client = new Client();
        $this->parser = new Parser();
        $this->config = new Config();
    }

    /**
     * @throws GuzzleException
     */
    public function recipes(?int $depth = null, ?string $term = null, ?string $sort = null, ?array $filters = null): array
    {
        $depth = $depth ?? $this->config->defaultDepth;
        $term = $term ?? $this->config->defaultTerm;
        $sort = $sort ?? $this->config->defaultSort;
        if ('' === $sort) {
            $sort = $this->config->defaultSort;
        }
        $filters = $filters ?? [];
        $this->client->resetRequestedUrls();

        $recipes = [];

        // Start crawling
        for ($i = 0; $i < $depth; ++$i) {
            // Fetch list of recipes for the given term and page
            $response = $this->client->list($term, $i, $sort, $filters);

            // Check if response is empty
            if (empty($response)) {
                echo 'No response received from crawl.'.PHP_EOL;

                continue;
            }

            // Parse URLs from response
            $urls = $this->parser->urls($response);

            // Check if any URLs were parsed
            if (empty($urls)) {
                echo 'No data parsed from response.'.PHP_EOL;

                continue;
            }

            // Fetch and parse each recipe
            foreach ($urls as $url) {
                // Crawl recipe details page
                $recipeHtml = $this->client->recipe($url);

                // Parse recipe details
                $recipes[] = $this->parser->recipe($recipeHtml);
            }
        }

        return $recipes;
    }
}
