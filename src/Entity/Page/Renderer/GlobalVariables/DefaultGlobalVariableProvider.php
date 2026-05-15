<?php

namespace KikCMS\Entity\Page\Renderer\GlobalVariables;

use KikCMS\Entity\Page\Page;
use Symfony\Component\HttpFoundation\Request;

class DefaultGlobalVariableProvider implements GlobalVariableProviderInterface
{
    public function provide(Request $request, ?Page $page = null): array
    {
        return [];
    }
}