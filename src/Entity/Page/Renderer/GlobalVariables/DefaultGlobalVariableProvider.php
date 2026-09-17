<?php

namespace KikCMS\Entity\Page\Renderer\GlobalVariables;

use KikCMS\Domain\Form\Config\FormConfig;
use KikCMS\Domain\FrontendForm\FrontendFormService;
use KikCMS\Entity\Page\Page;
use KikCMS\Entity\Page\PageRepository;
use Symfony\Component\HttpFoundation\Request;

readonly class DefaultGlobalVariableProvider implements GlobalVariableProviderInterface
{
    public function __construct(
        private PageRepository $pageRepository,
        private FrontendFormService $frontendFormService,
    ) {}

    public function provide(Request $request, ?Page $page = null): array
    {
        $params = [];

        if ($page->hasSectionType('overview')) {
            $children = $this->pageRepository->findByParent($page, 1);

            $params['children'] = $children;
        }

        foreach ($page->getSections() as $section) {
            if ($formClass = $this->getFormClassBySection($section)) {
                $params['renderedForm'] = $this->frontendFormService->handle($formClass, $request);
            }
        }

        return $params;
    }

    private function getFormClassBySection($section): ?string
    {
        if ($section->getType() !== 'form') {
            return null;
        }

        if ( ! $formName = $section->getContent()['form'] ?? null) {
            return null;
        }

        return FormConfig::FORM_CLASS_MAP[$formName] ?? null;
    }
}