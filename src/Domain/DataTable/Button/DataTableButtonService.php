<?php

namespace KikCMS\Domain\DataTable\Button;

use KikCMS\Domain\App\Config\ConfigService;
use KikCMS\Domain\DataTable\Config\DataTableConfig;

readonly class DataTableButtonService
{
    public function __construct(
        private ConfigService $configService,
    ){}

    public function normalize(array $buttons): array
    {
        $defaults = [
            'add' => [
                'action' => 'add',
                'icon'   => DataTableConfig::BUTTON_ADD_ICON,
            ],
            'delete' => [
                'action' => 'delete',
                'icon'   => DataTableConfig::BUTTON_DELETE_ICON,
            ],
        ];

        // set defaults
        foreach ($defaults as $key => $default) {
            if (isset($buttons[$key])) {
                $buttons[$key]['action'] = $buttons[$key]['action'] ?? $default['action'];
                $buttons[$key]['icon']   = $buttons[$key]['icon'] ?? $default['icon'];
            }
        }

        // translate item labels
        foreach ($buttons as &$button) {
            if(array_key_exists('items', $button)){
                $button['items'] = $this->configService->translateLabels($button['items']);
            }
        }

        return $this->configService->translateLabels($buttons);
    }
}