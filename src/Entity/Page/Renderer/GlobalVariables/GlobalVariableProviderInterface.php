<?php

namespace KikCMS\Entity\Page\Renderer\GlobalVariables;

use KikCMS\Entity\Page\Page;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpFoundation\Request;

#[AutoconfigureTag('page.global_variable_provider')]
interface GlobalVariableProviderInterface
{
    public function provide(Request $request, ?Page $page = null): array;
}