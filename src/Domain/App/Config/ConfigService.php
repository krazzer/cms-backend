<?php

namespace KikCMS\Domain\App\Config;

use Exception;
use KikCMS\Domain\App\Path\PathConfig;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Yaml;

readonly class ConfigService
{
    public function __construct(
        private KernelInterface $kernel,
        private Parser $yamlParser,
    ) {}

    public function getConfigFromFile(string $name): array
    {
        $filePath = $this->kernel->getCmsDir(PathConfig::DIR_CONFIG . '/' . $name . '.yaml');

        if ($config = $this->yamlParser->parseFile($filePath, Yaml::PARSE_CUSTOM_TAGS) ?? null) {
            return $config;
        }

        throw new Exception("No config found for Form '$name'");
    }
}