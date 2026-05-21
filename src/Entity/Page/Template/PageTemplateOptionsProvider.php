<?php

namespace KikCMS\Entity\Page\Template;

use KikCMS\Domain\App\Config\Provider\ConfigProviderInterface;
use KikCMS\Domain\App\Config\Provider\Context;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem('page_template_options')]
readonly class PageTemplateOptionsProvider implements ConfigProviderInterface
{
    public function __construct(private TemplateService $templateService) {}

    public function getConfig(Context $context): array
    {
        return $this->templateService->getMap();
    }
}