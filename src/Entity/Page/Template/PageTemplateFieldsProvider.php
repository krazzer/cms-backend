<?php

namespace KikCMS\Entity\Page\Template;

use KikCMS\Domain\App\Config\Provider\ConfigProviderInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem('page_template')]
class PageTemplateFieldsProvider implements ConfigProviderInterface
{
    public function getConfig(): array
    {
        return [
            'type'     => [
                'type'    => 'hidden',
                'default' => 'page',
            ],
            'title'    => [
                'type'      => 'text',
                'field'     => 'name.*',
                'label'     => 'Name',
                'validator' => ['name' => 'presence'],
            ],
            'sections' => [
                'type'     => 'datatable',
                'label'    => 'Content',
                'instance' => 'page_content',
            ],
            'text'     => [
                'field' => 'content.text',
                'type'  => 'textarea',
                'label' => 'Content textatea!',
            ],
        ];
    }
}