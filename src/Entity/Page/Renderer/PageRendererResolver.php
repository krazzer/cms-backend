<?php

namespace KikCMS\Entity\Page\Renderer;

use KikCMS\Entity\Page\Page;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

readonly class PageRendererResolver
{
    /** @param iterable<PageRendererInterface> $renderers */
    public function __construct(
        #[AutowireIterator('page.renderer')]
        private iterable $renderers
    ) {}

    public function resolve(Page $page): PageRendererInterface
    {
        foreach ($this->renderers as $renderer) {
            if ($renderer->supports($page)) {
                return $renderer;
            }
        }

        throw new RuntimeException('No renderer found for template: ' . $page->getTemplate());
    }
}