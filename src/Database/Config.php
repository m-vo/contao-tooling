<?php

declare(strict_types=1);

namespace Mvo\ContaoTooling\Database;

use Symfony\Component\Yaml\Yaml;
use Webmozart\PathUtil\Path;

class Config
{
    private string $file;

    public function __construct(string $file = null)
    {
        $this->file = $file ?? Path::join(getcwd(), '.database.yaml');
    }

    public function getIgnoredTables(): array
    {
        return $this->getConfig()['ignore'] ?? [];
    }

    public function getIgnoredData(): array
    {
        return $this->getConfig()['ignore_data'] ?? [];
    }

    public function getDirectory(): string
    {
        $directory = $this->getConfig()['path'] ?? '.database';

        return Path::makeAbsolute($directory, getcwd());
    }

    public function getPayloads(): array
    {
        $basePath = $this->getDirectory();

        return array_map(
            static fn(string $path) => Path::join($basePath, $path),
            $this->getConfig()['payloads'] ?? []
        );
    }

    private function getConfig(): array
    {
        $default = [
            'ignore' => [
                'tl_user',
            ],
            'ignore_data' => [
                'tl_crawl_queue',
                'tl_cron_job',
                'tl_log',
                'tl_opt_in',
                'tl_opt_in_related',
                'tl_search',
                'tl_search_index',
                'tl_search_term',
                'tl_undo',
                'tl_version',
            ]
        ];

        return file_exists($this->file) ? (array)Yaml::parseFile($this->file) : $default;
    }
}
