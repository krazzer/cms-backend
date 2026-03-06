<?php

namespace KikCMS\Entity\Page\Template;

use KikCMS\Domain\Form\Providers\FormFieldsProviderInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem('page_template')]
class PageTemplateFieldsProvider implements FormFieldsProviderInterface
{
    public function getFieldsConfig(): array
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