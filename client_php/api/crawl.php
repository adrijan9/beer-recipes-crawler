<?php

if ('POST' !== $_SERVER['REQUEST_METHOD']) {
    http_response_code(400);
    echo json_encode(['error' => 'Bad Request']);

    exit;
}

require __DIR__.'/../../vendor/autoload.php';

use Crawler\Config;
use Crawler\Exceptions\ToResponse;
use Crawler\File;
use Crawler\Recipes\Crawl;

try {
    // -----------------
    // Construct config
    // -----------------
    $config = new Config();

    $depth = $_GET['depth'] ?? $config->defaultDepth;
    $term = $_GET['term'] ?? $config->defaultTerm;
    $name = $_GET['name'] ?? null;
    if (null !== $name) {
        $name = trim($name);
        if ('' === $name) {
            $name = null;
        }
    }
    if (null !== $name && !preg_match('/^[A-Za-z0-9_-]+$/', $name)) {
        throw new InvalidArgumentException('File name can only use letters, numbers, dash (-), and underscore (_).');
    }
    $sort = $_GET['sort'] ?? $config->defaultSort;
    if ('' === $sort) {
        $sort = $config->defaultSort;
    }
    $rated = null;
    if (array_key_exists('rated', $_GET)) {
        $rated = $_GET['rated'];
        if ('' === $rated) {
            $rated = null;
        }
    }
    $recipeTypeKeys = array_keys($config->recipeTypeLabels);
    $recipeTypes = [];
    foreach ($recipeTypeKeys as $key) {
        if (isset($_GET[$key])) {
            $recipeTypes[$key] = $_GET[$key];
        }
    }
    $filters = $recipeTypes;
    if (null !== $rated) {
        $filters['rated'] = $rated;
    }

    // ------------------
    // Construct crawler
    // ------------------
    $crawl = new Crawl();

    // Collected recipes
    $recipes = $crawl->recipes($depth, $term, $sort, $filters);
    $urls = $crawl->client->requestedUrls();

    // ---------------
    // Construct file
    // ---------------
    new File()->save($recipes, $term, $depth, $sort, $recipeTypes, $rated, $urls, $name);

    header('HTTP/1.1 204 No Content');
} catch (Throwable $exception) {
    ToResponse::make($exception);
}
