<?php

namespace KikCMS\Entity\PageSection\Provider;

use KikCMS\Domain\App\Config\Provider\ConfigProviderInterface;
use KikCMS\Domain\App\Config\Provider\Context;
use KikCMS\Domain\DataTable\Context\DataContext;
use KikCMS\Domain\DataTable\Modifier\DataTableCellModifier;
use KikCMS\Entity\PageSection\PageSectionConfigService;

readonly class PageSectionRowViewProvider implements ConfigProviderInterface
{
    public function __construct(
        private PageSectionConfigService $pageSectionConfigService,
        private DataTableCellModifier $dataTableCellModifier
    ) {}

    public function getConfig(DataContext|Context $context): array
    {
        $sectionNameMap = $this->pageSectionConfigService->getSectionNameMap();

        $this->dataTableCellModifier->modify($context, 'type', function ($value) use ($sectionNameMap) {
            return $sectionNameMap[$value] ?? $value;
        });

        return $context->getData();
    }
}