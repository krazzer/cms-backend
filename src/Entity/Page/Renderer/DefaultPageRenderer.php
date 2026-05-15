<?php

namespace KikCMS\Entity\Page\Renderer;

use KikCMS\Entity\Page\Page;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpFoundation\Request;

#[AutoconfigureTag('page.renderer')]
class DefaultPageRenderer implements PageRendererInterface
{
    public function supports(Page $page): bool
    {
        return true;
    }

    public function render(Page $page, Request $request): RenderResult
    {
        return new ViewRenderResult('theme/templates/default.twig', [
            'page'  => $page,
            'title' => $page->getName()[$request->getLocale()],
        ]);
    }
}