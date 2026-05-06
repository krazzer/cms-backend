<?php

namespace KikCMS\Domain\Menu;

use KikCMS\Domain\Frontend\FrontendConfig;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MenuExtension extends AbstractExtension
{
    public function __construct(readonly private MenuService $menuService) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('menu', $this->getMainMenu(...)),
        ];
    }

    public function getMainMenu(string|int $id = FrontendConfig::MENU_MAIN, ?int $maxLevel = 1): array
    {
        return $this->menuService->get($id, $maxLevel);
    }
}