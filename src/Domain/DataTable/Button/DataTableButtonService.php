<?php

namespace KikCMS\Domain\DataTable\Button;

use KikCMS\Domain\App\Config\ConfigService;
use KikCMS\Domain\DataTable\Config\DataTableConfig;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class DataTableButtonService
{
    public function __construct(
        private ConfigService $configService,
        private TranslatorInterface $translator,
    ) {}

    public function normalize(?array $buttons): array
    {
        // if no buttons are defined, use the default
        if($buttons === null){
            $buttons = ['add' => null, 'delete' => null];
        }

        $defaults = [
            'add'    => [
                'action' => 'add',
                'icon'   => DataTableConfig::BUTTON_ADD_ICON,
                'label'  => $this->translator->trans('datatable.button.add'),
            ],
            'delete' => [
                'action' => 'delete',
                'icon'   => DataTableConfig::BUTTON_DELETE_ICON,
                'label'  => $this->translator->trans('datatable.button.delete'),
            ],
        ];

        // set default properties if not defined
        foreach ($defaults as $key => $default) {
            if (array_key_exists($key, $buttons)) {
                $buttons[$key]['action'] = $buttons[$key]['action'] ?? $default['action'];
                $buttons[$key]['icon']   = $buttons[$key]['icon'] ?? $default['icon'];
                $buttons[$key]['label']  = $buttons[$key]['label'] ?? $default['label'];
            }
        }

        // translate item labels
        foreach ($buttons as &$button) {
            if (array_key_exists('items', $button)) {
                $button['items'] = $this->configService->translateLabels($button['items']);
            }
        }

        return $this->configService->translateLabels($buttons);
    }
}