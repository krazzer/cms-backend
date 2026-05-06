<?php

namespace KikCMS\Entity\PageSection\Provider;

use KikCMS\Domain\App\Config\Provider\ConfigProviderInterface;
use KikCMS\Domain\App\Config\Provider\Context;
use KikCMS\Entity\PageSection\PageSectionConfigService;

readonly class PageSectionTypeOptionsProvider implements ConfigProviderInterface
{
    public function __construct(
        private PageSectionConfigService $pageSectionConfigService
    ) {}

    public function getConfig(Context $context): array
    {
        return $this->pageSectionConfigService->getSectionNameMap();
    }
}