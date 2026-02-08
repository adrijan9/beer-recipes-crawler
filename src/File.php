<?php

namespace Crawler;

/**
 * Class File.
 */
class File
{
    private Config $config;

    public function __construct()
    {
        $this->config = new Config();
    }

    /**
     * @throws \JsonException
     */
    public function save(
        array $recipes,
        string $term,
        int $depth,
        ?string $sort = null,
        ?array $types = null,
        ?string $rated = null,
        ?array $urls = null,
        ?string $name = null
    ): void {
        // Create recipes directory if it does not exist
        $recipesDir = $this->config->recipesDir;
        if ((false === is_dir($recipesDir)) && !mkdir($recipesDir, 0777, true) && !is_dir($recipesDir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $recipesDir));
        }

        // Save recipes to JSON file
        if ($name) {
            $filename = $recipesDir.'/'.$name.'.json';
        } else {
            $filename = $recipesDir.'/recipes_'.date('Y_m_d_H_i_s').'_'.$term.'_'.$depth.'.json';
        }

        $data = [
            'term' => $term,
            'depth' => $depth,
            'sort' => $sort,
            'types' => $types,
            'rated' => $rated,
            'urls' => $urls,
            'timestamp' => date('Y-m-d H:i:s'),
            'recipes' => $recipes,
        ];

        file_put_contents($filename, json_encode($data, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
    }
}
