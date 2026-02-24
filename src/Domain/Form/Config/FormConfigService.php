<?php

namespace KikCMS\Domain\Form\Config;

use Exception;
use KikCMS\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Yaml;

readonly class FormConfigService
{
    public function __construct(
        private KernelInterface $kernel,
        private Parser $yamlParser
    ) {}

    public function getByName(string $name): array
    {
        $filePath = $this->kernel->getCmsDir(Kernel::DIR_CONFIG_FORMS . DIRECTORY_SEPARATOR . $name . '.yaml');

        if ( ! $config = $this->yamlParser->parseFile($filePath, Yaml::PARSE_CUSTOM_TAGS) ?? null) {
            throw new Exception("No config found for Form '$name'");
        }

        return $config;
    }
}