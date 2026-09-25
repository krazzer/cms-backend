<?php

namespace KikCMS\Domain\App\Config;

use Exception;
use KikCMS\Domain\App\Path\PathConfig;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class ConfigService
{
    public function __construct(
        private KernelInterface $kernel,
        private Parser $yamlParser,
        private TranslatorInterface $translator,
    ) {}

    public function getByName(string $name): array
    {
        $filePath = $this->kernel->getCmsDir(PathConfig::DIR_CONFIG . '/' . $name . '.yaml');

        return $this->getByPath($filePath);
    }

    /**
     * Merges the base CMS config with the app config if it exists.
     * If $mergeAdd is true, adds all base config properties that are not set in the app config
     */
    public function getMerged(string $name, bool $mergeAdd = false): array
    {
        $baseConfig = $this->getByName($name);

        if ( ! $this->kernel->isProject()) {
            return $baseConfig;
        }

        $appConfigPath = $this->getFilePathByNameApp($name);

        if ( ! file_exists($appConfigPath)) {
            return $baseConfig;
        }

        $appConfig = $this->getByPath($appConfigPath);

        $finalConfig = [];

        foreach ($appConfig as $key => $customProps) {
            $customProps = $customProps ?? [];
            $baseProps   = $baseConfig[$key] ?? [];

            $finalConfig[$key] = array_merge($baseProps, $customProps);
        }

        if($mergeAdd){
            foreach ($baseConfig as $key => $baseProps) {
                if ( ! isset($finalConfig[$key])) {
                    $finalConfig[$key] = $baseProps;
                }
            }
        }

        foreach ($finalConfig as &$item) {
            if (isset($item['label_trans'])) {
                $item['label'] = $this->translator->trans($item['label_trans']);
            }
        }

        return $finalConfig;
    }

    public function getByNameApp(string $name): array
    {
        return $this->getByPath($this->getFilePathByNameApp($name));
    }

    public function getFilePathByNameApp(string $name): string
    {
        return $this->kernel->getAppDir(PathConfig::DIR_CONFIG . '/' . $name . '.yaml');
    }

    public function getByPath(string $filePath): array
    {
        if ($config = $this->yamlParser->parseFile($filePath, Yaml::PARSE_CUSTOM_TAGS) ?? null) {
            return $config;
        }

        throw new Exception("No config file found at '$filePath'");
    }
}