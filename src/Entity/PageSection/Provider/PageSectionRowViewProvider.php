<?php

namespace KikCMS\Entity\PageSection\Provider;

use KikCMS\Domain\App\Config\Provider\ConfigProviderInterface;
use KikCMS\Domain\App\Config\Provider\Context;
use KikCMS\Domain\DataTable\Context\DataContext;
use KikCMS\Entity\PageSection\PageSectionConfigService;

readonly class PageSectionRowViewProvider implements ConfigProviderInterface
{
    public function __construct(
        private PageSectionConfigService $pageSectionConfigService
    ) {}

    public function getConfig(DataContext|Context $context): array
    {
        $sectionNameMap = $this->pageSectionConfigService->getSectionNameMap();

        $rows    = $context->getData();
        $headers = $context->getDataTable()->getHeaders();

        $typeIndex = array_search('type', array_keys($headers));

        foreach ($rows as $i => $row) {
            $type = $row['data'][$typeIndex];
            $type = $sectionNameMap[$type] ?? $type;

            $rows[$i]['data'][$typeIndex] = $type;
        }

        return $rows;
    }
}