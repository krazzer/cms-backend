<?php

namespace KikCMS\Entity\Page;

use KikCMS\Domain\App\Config\Provider\ConfigProviderInterface;
use KikCMS\Domain\Form\Config\FormConfigService;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem('pageForm')]
readonly class PageFormProvider implements ConfigProviderInterface
{
    public function __construct(private FormConfigService $formConfigService) {}

    public function getConfig(): array
    {
        return $this->formConfigService->getConfigFromFile('page');
    }
}