<?php

namespace KikCMS\Domain\App\CmsMenu;

use KikCMS\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Parser;

readonly class CmsMenuService
{
    public function __construct(
        private KernelInterface $kernel,
        private Parser $yamlParser,
    ) {}

    public function getMenu(): array
    {
        $filePath = $this->kernel->getCmsDir(Kernel::DIR_CONFIG . DIRECTORY_SEPARATOR . 'menu.yaml');

        return $this->yamlParser->parseFile($filePath);
    }
}