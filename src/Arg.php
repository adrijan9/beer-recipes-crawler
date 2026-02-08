<?php

namespace Crawler;

/**
 * Class Arg.
 */
class Arg
{
    public Config $config;

    public function __construct(private readonly int $argc, private readonly array $argv)
    {
        $this->config = new Config();
    }

    public function depth(): int
    {
        return (int) $this->get('--depth') ?: $this->config->defaultDepth;
    }

    public function term()
    {
        return $this->get('--term') ?: $this->config->defaultTerm;
    }

    public function sort(): string
    {
        return $this->get('--sort') ?: $this->config->defaultSort;
    }

    public function rated(): ?int
    {
        $rated = $this->get('--rated');
        if (null === $rated || '' === $rated) {
            return null;
        }

        $rated = (int) $rated;

        if ($rated < 0 || $rated > 5) {
            throw new \InvalidArgumentException('Rated must be an integer between 0 and 5.');
        }

        return $rated;
    }

    public function types(): ?array
    {
        $types = $this->get('--types');
        if (null === $types || '' === $types) {
            return null;
        }

        $values = array_filter(array_map('trim', explode(',', (string) $types)));
        if (empty($values)) {
            return null;
        }

        $typesMap = [];
        foreach ($values as $value) {
            $typesMap[$value] = 1;
        }

        return $typesMap;
    }

    public function get(string $arg): mixed
    {
        if ($this->argc <= 1) {
            return null;
        }

        foreach ($this->argv as $index => $value) {
            if ($value === $arg && isset($this->argv[$index + 1])) {
                $next = $this->argv[$index + 1];
                if (str_starts_with($next, '--')) {
                    return null;
                }

                return $next;
            }
        }

        return null;
    }

    public function has(string $arg): bool
    {
        foreach ($this->argv as $value) {
            if ($value === $arg) {
                return true;
            }
        }

        return false;
    }

    public function missingValueFor(string $arg): bool
    {
        foreach ($this->argv as $index => $value) {
            if ($value !== $arg) {
                continue;
            }

            if (!isset($this->argv[$index + 1])) {
                return true;
            }

            $next = $this->argv[$index + 1];
            if (str_starts_with($next, '--')) {
                return true;
            }

            return false;
        }

        return false;
    }
}
