<?php

if ('DELETE' !== $_SERVER['REQUEST_METHOD'] || !isset($_GET['recipe'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Bad Request']);

    exit;
}

require __DIR__.'/../../vendor/autoload.php';

use Crawler\Exceptions\ToResponse;

try {
    $recipe = $_GET['recipe'];

    header('Content-type: application/json');

    if (!file_exists($recipe)) {
        http_response_code(404);
        echo json_encode(['error' => 'File Not Found'], JSON_THROW_ON_ERROR);

        exit;
    }

    if (unlink($recipe)) {
        echo json_encode(['success' => true], JSON_THROW_ON_ERROR);

        return;
    }

    http_response_code(500);
    echo json_encode(['error' => 'Could not delete file'], JSON_THROW_ON_ERROR);
} catch (Throwable $exception) {
    ToResponse::make($exception);
}
