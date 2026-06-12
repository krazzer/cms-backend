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
        $cmsMenuPath = $this->kernel->getCmsDir(Kernel::DIR_CONFIG . DIRECTORY_SEPARATOR . 'menu.yaml');
        $appMenuPath = $this->kernel->getAppDir(Kernel::DIR_CONFIG . DIRECTORY_SEPARATOR . 'menu.yaml');

        $baseMenu = $this->yamlParser->parseFile($cmsMenuPath);

        if ( ! file_exists($appMenuPath)) {
            return $baseMenu;
        }

        $customMenu = $this->yamlParser->parseFile($appMenuPath);
        $finalMenu  = [];

        foreach ($customMenu as $key => $customProps) {
            $customProps = $customProps ?? [];
            $baseProps   = $baseMenu[$key] ?? [];

            $finalMenu[$key] = array_merge($baseProps, $customProps);
        }

        return $finalMenu;
    }
}