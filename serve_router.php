<?php

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$uri = urldecode($uri);

if (str_starts_with($uri, '/api/')) {
    $apiPath = __DIR__.'/client_php'.$uri;
    if (is_file($apiPath)) {
        require $apiPath;

        return true;
    }

    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Not Found']);

    return true;
}

$publicDir = __DIR__.'/client';
$publicDirReal = realpath($publicDir);

if ($publicDirReal) {
    $requested = $publicDirReal.('/' === $uri ? '/index.html' : $uri);
    $requestedReal = realpath($requested);
    if ($requestedReal && str_starts_with($requestedReal, $publicDirReal) && is_file($requestedReal)) {
        return false;
    }
}

$indexFile = $publicDir.'/index.html';
if (is_file($indexFile)) {
    header('Content-Type: text/html');
    readfile($indexFile);

    return true;
}

http_response_code(404);
echo 'Not Found';
