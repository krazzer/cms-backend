<?php

namespace KikCMS\Entity\PageSection;

use KikCMS\Domain\App\Config\Provider\ConfigProviderInterface;
use KikCMS\Domain\App\Config\Provider\Context;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem('section_type_options')]
readonly class TypeOptionsProvider implements ConfigProviderInterface
{
    public function __construct(
        private PageSectionConfigService $pageSectionConfigService
    ) {}

    public function getConfig(Context $context): array
    {
        return $this->pageSectionConfigService->getSectionNameMap();
    }
}