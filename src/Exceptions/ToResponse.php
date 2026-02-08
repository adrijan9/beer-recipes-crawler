<?php

namespace Crawler\Exceptions;

/**
 * Class ToResponse.
 */
class ToResponse
{
    /**
     * @throws \JsonException
     */
    public function __construct(\Throwable $throwable)
    {
        $code = $throwable->getCode();

        header('Content-Type: application/json');
        http_response_code($code);
        echo json_encode(['error' => $throwable->getMessage()], JSON_THROW_ON_ERROR);
    }

    /**
     * @throws \JsonException
     */
    public static function make(\Throwable $throwable): self
    {
        return new self($throwable);
    }
}
