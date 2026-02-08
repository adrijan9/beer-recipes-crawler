<?php

if ('DELETE' !== $_SERVER['REQUEST_METHOD']) {
    http_response_code(400);
    echo json_encode(['error' => 'Bad Request']);

    exit;
}

require __DIR__.'/../../vendor/autoload.php';

use Crawler\Config;
use Crawler\Exceptions\ToResponse;

try {
    $config = new Config();
    $recipesDir = rtrim($config->recipesDir, '/');
    $files = glob($recipesDir.'/*.json') ?: [];
    $deleted = 0;
    $errors = [];

    foreach ($files as $file) {
        if (!is_file($file)) {
            continue;
        }

        if (unlink($file)) {
            ++$deleted;
        } else {
            $errors[] = $file;
        }
    }

    header('Content-type: application/json');

    $success = 0 === count($errors);
    if (!$success) {
        http_response_code(500);
    }

    echo json_encode(
        [
            'success' => $success,
            'deleted' => $deleted,
            'errors' => $errors,
        ],
        JSON_THROW_ON_ERROR
    );
} catch (Throwable $exception) {
    ToResponse::make($exception);
}
