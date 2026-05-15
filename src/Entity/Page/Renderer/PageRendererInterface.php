<?php

namespace KikCMS\Entity\Page\Renderer;

use KikCMS\Entity\Page\Page;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpFoundation\Request;

#[AutoconfigureTag('page.renderer')]
interface PageRendererInterface
{
    public function supports(Page $page): bool;

    public function render(Page $page, Request $request): RenderResult;
}