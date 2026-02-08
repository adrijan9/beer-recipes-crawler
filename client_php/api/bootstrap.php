<?php

if ('GET' !== $_SERVER['REQUEST_METHOD']) {
    http_response_code(400);
    echo json_encode(['error' => 'Bad Request']);

    exit;
}

require __DIR__.'/../../vendor/autoload.php';

use Crawler\Config;
use Crawler\Exceptions\ToResponse;

try {
    $config = new Config();
    $jsonFiles = glob($config->recipesDir.'/*.json') ?: [];
    sort($jsonFiles);

    header('Content-type: application/json');

    echo json_encode(
        [
            'config' => $config->uiConfig(),
            'files' => $jsonFiles,
        ],
        JSON_THROW_ON_ERROR
    );
} catch (Throwable $exception) {
    ToResponse::make($exception);
}
