<?php

namespace KikCMS\Entity\Page;

use KikCMS\Domain\App\Config\Provider\ConfigProviderInterface;
use KikCMS\Domain\App\Config\Provider\Context;
use KikCMS\Domain\DataTable\Dto\Context\FormContext;
use KikCMS\Domain\Form\Config\FormConfigService;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem('pageForm')]
readonly class PageFormProvider implements ConfigProviderInterface
{
    public function __construct(private FormConfigService $formConfigService) {}

    public function getConfig(FormContext|Context $context): array
    {
        return match ($context->getType()) {
            'link' => $this->formConfigService->getConfigFromFile('link'),
            'menu' => $this->formConfigService->getConfigFromFile('menu'),
            default => $this->formConfigService->getConfigFromFile('page'),
        };
    }
}