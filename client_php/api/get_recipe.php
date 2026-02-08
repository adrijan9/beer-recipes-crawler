<?php

if ('GET' !== $_SERVER['REQUEST_METHOD'] || !isset($_GET['recipe'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Bad Request']);

    exit;
}

require __DIR__.'/../../vendor/autoload.php';

use Crawler\Exceptions\ToResponse;

try {
    header('Content-type: application/json');

    $recipe = $_GET['recipe'];

    if (!file_exists($recipe)) {
        throw new Exception('File Not Found', 404);
    }

    $contents = file_get_contents($recipe);
    if (false === $contents) {
        throw new Exception('Could not read file', 500);
    }

    echo $contents;
} catch (Throwable $exception) {
    ToResponse::make($exception);
}
