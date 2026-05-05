<?php

namespace KikCMS\Entity\PageSection;

use KikCMS\Domain\App\Config\Provider\ConfigProviderInterface;
use KikCMS\Domain\App\Config\Provider\Context;
use KikCMS\Domain\DataTable\Dto\Context\FormContext;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem('section_type_fields')]
readonly class PageSectionTypeFieldsProvider implements ConfigProviderInterface
{
    public function __construct(
        private PageSectionRepository $repository, private PageSectionConfigService $pageSectionConfigService
    ) {}

    public function getConfig(FormContext|Context $context): array
    {
        $type = $context->getType() ?? null;

        if ( ! $context->getTrigger() && ($id = $context->getId()) && ($section = $this->repository->find($id))) {
            $type = $section->getType();
        }

        if( ! $type ) {
            return [];
        }

        return $this->pageSectionConfigService->getFieldsByType($type);
    }
}