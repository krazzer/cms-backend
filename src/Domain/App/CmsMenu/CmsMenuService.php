<?php

namespace KikCMS\Domain\App\CmsMenu;

use KikCMS\Domain\App\Config\ConfigService;

readonly class CmsMenuService
{
    public function __construct(
        private ConfigService $configService,
    ) {}

    public function getMenu(): array
    {
        return $this->configService->getMerged('menu');
    }
}