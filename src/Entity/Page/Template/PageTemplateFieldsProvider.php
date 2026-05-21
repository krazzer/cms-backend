<?php

namespace KikCMS\Entity\Page\Template;

use KikCMS\Domain\App\Config\Provider\ConfigProviderInterface;
use KikCMS\Domain\App\Config\Provider\Context;
use KikCMS\Domain\DataTable\Context\FormContext;
use KikCMS\Entity\Page\PageRepository;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem('page_template')]
readonly class PageTemplateFieldsProvider implements ConfigProviderInterface
{
    public function __construct(
        private PageRepository $pageRepository,
        private TemplateService $templateService
    ) {}

    public function getConfig(FormContext|Context $context): array
    {
        $template = $context->getValue('template') ?? 'default';

        if ( ! $context->getTrigger() && ($id = $context->getId()) && ($page = $this->pageRepository->find($id))) {
            $template = $page->getTemplate();
        }

        return $this->templateService->getFieldsByTemplate($template);
    }
}