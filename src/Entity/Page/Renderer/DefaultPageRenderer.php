<?php

namespace KikCMS\Entity\Page\Renderer;

use KikCMS\Entity\Page\Page;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;

#[AutoconfigureTag('page.renderer')]
readonly class DefaultPageRenderer implements PageRendererInterface
{
    public function __construct(private Environment $twig) {}

    public function supports(Page $page): bool
    {
        return true;
    }

    public function render(Page $page, Request $request): RenderResult
    {
        $relativeTemplate = 'theme/templates/' . $page->getTemplate() . '.twig';

        $template = '@cms/' . $relativeTemplate;

        if ($this->twig->getLoader()->exists('@app/' . $relativeTemplate)) {
            $template = '@app/' . $relativeTemplate;
        }

        return new ViewRenderResult($template, [
            'page'  => $page,
            'title' => $page->getName()[$request->getLocale()] ?? null,
        ]);
    }
}