<?php

namespace KikCMS\Entity\Page\Renderer\GlobalVariables;

use KikCMS\Entity\Page\Page;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpFoundation\Request;

readonly class GlobalVariableResolver
{
    public function __construct(
        #[AutowireIterator('page.global_variable_provider')] private iterable $providers,
    ) {}

    public function resolve(Request $request, ?Page $page = null): array
    {
        $variables = [];

        foreach ($this->providers as $provider) {
            $variables = array_replace_recursive($variables, $provider->provide($request, $page));
        }

        return $variables;
    }
}