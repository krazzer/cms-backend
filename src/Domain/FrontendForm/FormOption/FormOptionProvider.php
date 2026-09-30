<?php

namespace KikCMS\Domain\FrontendForm\FormOption;

use KikCMS\Domain\App\Config\Provider\ConfigProviderInterface;
use KikCMS\Domain\App\Config\Provider\Context;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem('form_options')]
readonly class FormOptionProvider implements ConfigProviderInterface
{
    public function __construct(private FormOptionService $formOptionService) {}

    public function getConfig(Context $context): array
    {
        return $this->formOptionService->getNameMap();
    }
}