<?php

namespace KikCMS\Entity\Page\Template;

use KikCMS\Domain\App\Config\Provider\ConfigProviderInterface;
use KikCMS\Domain\App\Config\Provider\Context;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem('page_template_options')]
class PageTemplateOptionsProvider implements ConfigProviderInterface
{
    public function getConfig(Context $context): array
    {
        return [
            'home'    => 'Home',
            'default' => 'Default',
        ];
    }
}