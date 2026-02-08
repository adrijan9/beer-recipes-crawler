<?php

namespace Crawler;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Client.
 */
class Client
{
    public GuzzleClient $client;
    private Config $config;
    private array $requestedUrls = [];

    public function __construct()
    {
        $this->client = new GuzzleClient();
        $this->config = new Config();
    }

    public function requestedUrls(): array
    {
        return $this->requestedUrls;
    }

    public function resetRequestedUrls(): void
    {
        $this->requestedUrls = [];
    }

    /**
     * @throws GuzzleException
     */
    public function list(?string $term = null, int $page = 0, ?string $sort = null, array $filters = []): string
    {
        $term = $term ?? $this->config->defaultTerm;

        $url = $this->config->url.'/'.$page;

        $message = "Crawling URL: {$url} with term: {$term}";
        if (null !== $sort && '' !== $sort) {
            $message .= " and sort: {$sort}";
        }
        echo $message."\n";

        $query = [
            'term' => $term,
        ];
        if (null !== $sort && '' !== $sort) {
            $query['sort'] = $sort;
        }
        foreach ($filters as $key => $value) {
            if (null === $value || '' === $value) {
                continue;
            }
            $query[$key] = $value;
        }

        $response = $this->get($url, $query);

        return $response->getBody()->getContents();
    }

    /**
     * @throws GuzzleException
     */
    public function recipe(string $url): string
    {
        $response = $this->get($url, []);

        return $response->getBody()->getContents();
    }

    /**
     * @throws GuzzleException
     */
    public function get(string $url, array $data, array $headers = []): ResponseInterface
    {
        $fullUrl = $url;
        if (!empty($data)) {
            $queryString = http_build_query($data);
            if ('' !== $queryString) {
                $separator = str_contains($url, '?') ? '&' : '?';
                $fullUrl = $url.$separator.$queryString;
            }
        }
        $this->requestedUrls[] = $fullUrl;

        return $this->client->request('GET', $url, [
            'query' => $data,
            'headers' => $headers,
        ]);
    }
}
