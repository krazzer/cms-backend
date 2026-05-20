<?php

namespace KikCMS\Entity\Page\Renderer\GlobalVariables;

use KikCMS\Entity\Page\Page;
use KikCMS\Entity\Page\PageRepository;
use Symfony\Component\HttpFoundation\Request;

readonly class DefaultGlobalVariableProvider implements GlobalVariableProviderInterface
{
    public function __construct(private PageRepository $pageRepository) {}

    public function provide(Request $request, ?Page $page = null): array
    {
        if ($page->hasSectionType('overview')) {
            $children = $this->pageRepository->findByParent($page, 1);

            return ['children' => $children];
        }

        return [];
    }
}