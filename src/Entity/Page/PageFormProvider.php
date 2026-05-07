<?php

namespace KikCMS\Entity\Page;

use KikCMS\Domain\App\Config\Provider\ConfigProviderInterface;
use KikCMS\Domain\App\Config\Provider\Context;
use KikCMS\Domain\DataTable\Context\FormContext;
use KikCMS\Domain\Form\Config\FormConfigService;

readonly class PageFormProvider implements ConfigProviderInterface
{
    public function __construct(
        private FormConfigService $formConfigService,
        private PageRepository $pageRepository
    ) {}

    public function getConfig(FormContext|Context $context): array
    {
        if (($id = $context->getId()) && ($page = $this->pageRepository->find($id))) {
            $type = $page->getType();
        } else {
            $type = $context->getType();
        }

        return match ($type) {
            'link' => $this->formConfigService->getConfigFromFile('link'),
            'menu' => $this->formConfigService->getConfigFromFile('menu'),
            default => $this->formConfigService->getConfigFromFile('page'),
        };
    }
}