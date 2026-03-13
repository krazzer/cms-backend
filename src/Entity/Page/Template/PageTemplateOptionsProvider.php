<?php

namespace KikCMS\Entity\Page\Template;

use KikCMS\Domain\App\Config\Provider\ConfigProviderInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem('page_template_options')]
class PageTemplateOptionsProvider implements ConfigProviderInterface
{
    public function getConfig(): array
    {
        return [
            'home'    => 'Home',
            'default' => 'Default',
        ];
    }
}