<?php

namespace KikCMS\Entity\Page\Template;

use KikCMS\Domain\App\Config\Provider\ConfigProviderInterface;
use KikCMS\Domain\App\Config\Provider\Context;
use KikCMS\Domain\DataTable\Dto\Context\FormContext;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem('page_template')]
class PageTemplateFieldsProvider implements ConfigProviderInterface
{
    public function getConfig(FormContext|Context $context): array
    {
        dlog($context->getTrigger());
        dlog($context->getData());

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